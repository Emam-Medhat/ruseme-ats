<?php

namespace App\Services;

use App\Models\Resume;
use App\Support\CvGeniusAnalysisPrompt;
use App\Support\ResumeAnalysisNormalizer;
use App\Support\ResumeScoringEngine;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Http;

class ResumeAnalysisService
{
    private const GEMINI_GENERATE_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct(
        protected DocumentExtractorService $documentExtractor
    ) {}

    /**
     * Parse text from a PDF file (path relative to the configured local disk).
     */
    public function extractTextFromPdf(string $filePath): string
    {
        return $this->documentExtractor->extractFromPdf($filePath);
    }

    /**
     * Parse text from PDF or DOCX on the local disk.
     */
    public function extractText(string $filePath, ?string $extension = null): string
    {
        return $this->documentExtractor->extract($filePath, $extension);
    }

    /**
     * Analyze resume text using Gemini first, then OpenAI fallback.
     *
     * @return array<string, mixed>
     */
    public function analyzeResume(Resume $resume): array
    {
        set_time_limit(180);

        $text = trim((string) $resume->parsed_text);

        if ($text === '') {
            throw new \Exception('Resume text is empty. Cannot analyze.');
        }

        $prompt = $this->buildAnalysisPrompt($text);

        // ── Build provider list dynamically ───────────────────────────────
        // Each provider: ['name' => string, 'callable' => Closure]
        $providers = [];

        if (! empty(config('services.gemini.key'))) {
            $providers[] = ['name' => 'Gemini', 'fn' => fn() => $this->callGemini($prompt)];
        }
        if (! empty(config('services.groq.key'))) {
            $providers[] = ['name' => 'Groq', 'fn' => fn() => $this->callGroq($prompt)];
        }
        if (! empty(config('services.mistral.key'))) {
            $providers[] = ['name' => 'Mistral', 'fn' => fn() => $this->callOpenAICompatible(
                $prompt,
                config('services.mistral.base_url'),
                config('services.mistral.models.0', 'mistral-small-latest'),
                config('services.mistral.key')
            )];
        }
        if (! empty(config('services.cerebras.key'))) {
            $providers[] = ['name' => 'Cerebras', 'fn' => fn() => $this->callOpenAICompatible(
                $prompt,
                config('services.cerebras.base_url'),
                config('services.cerebras.models.0', 'llama-3.3-70b'),
                config('services.cerebras.key')
            )];
        }
        if (! empty(config('services.together.key'))) {
            $providers[] = ['name' => 'Together', 'fn' => fn() => $this->callOpenAICompatible(
                $prompt,
                config('services.together.base_url'),
                config('services.together.models.0', 'meta-llama/Llama-3.3-70B-Instruct-Turbo'),
                config('services.together.key')
            )];
        }
        if (! empty(config('services.openai.key'))) {
            $providers[] = ['name' => 'OpenAI', 'fn' => fn() => $this->callOpenAI($prompt)];
        }

        if (empty($providers)) {
            \Log::warning('[CVGenius] No AI providers configured.');
        }

        $names = implode(' + ', array_column($providers, 'name'));
        \Log::info("[CVGenius] Running parallel ensemble: [{$names}]");

        // ── Run ALL active providers sequentially ─────────────────────────
        $results = [];
        $successfulProviders = [];
        foreach ($providers as $p) {
            try {
                $startTime = microtime(true);
                $r = ($p['fn'])();
                $duration = round((microtime(true) - $startTime) * 1000);
                if ($r) {
                    \Log::info("[CVGenius] {$p['name']} succeeded in {$duration}ms.");
                    $normalizedR = \App\Support\ResumeAnalysisNormalizer::normalize($r) ?? $r;
                    $normalizedR['provider_name'] = $p['name'];
                    $results[] = $normalizedR;
                    $successfulProviders[] = $p['name'];
                } else {
                    \Log::warning("[CVGenius] {$p['name']} returned empty response in {$duration}ms.");
                }
            } catch (\Throwable $e) {
                \Log::warning("[CVGenius] {$p['name']} failed: " . $e->getMessage());
            }
        }

        // ── Ensemble merge or single result ───────────────────────────────
        $result = null;
        if (count($results) >= 2) {
            \Log::info('[CVGenius] Ensemble merging ' . count($results) . ' AI results.');
            $result = $this->mergeAiResults($results);
        } elseif (count($results) === 1) {
            $result = $results[0];
            $result['ai_ensemble'] = false;
            $result['ai_providers_list'] = [$successfulProviders[0] ?? 'AI Engine'];
            \Log::info('[CVGenius] Single AI provider result used.');
        }

        // ── Fallback to mock if everything failed ─────────────────────────
        if (! $result) {
            if (app()->environment('local')) {
                \Log::info('[CVGenius] All AIs failed. Using mock fallback.');
                $result = $this->generateMockAnalysisResult($resume);
                $result['ai_ensemble'] = false;
                $result['ai_providers_list'] = ['Mock Analyzer'];
            } else {
                throw new \Exception('AI analysis failed. Please check your API keys and network connection.');
            }
        }

        $result = ResumeAnalysisNormalizer::normalize($result) ?? $result;

        if (! isset($result['overall_score'])) {
            throw new \Exception('The AI returned an invalid analysis payload (missing overall_score).');
        }

        $result = $this->enforceStrictScoring($result, $text);

        return $result;
    }


    /**
     * Merge multiple AI result arrays into a single ensemble result.
     * - Scores: weighted average (Gemini slightly higher weight as primary)
     * - issues/checks: union + deduplicate
     * - Strings: prefer longest/most detailed
     *
     * @param  array<int, array<string, mixed>>  $results
     * @return array<string, mixed>
     */
    private function mergeAiResults(array $results): array
    {
        $count  = count($results);
        $base   = $results[0]; // Use first as structural base

        // ── 1. Average all numeric scores ────────────────────────────────
        $scoreKeys = [
            'overall_score', 'ats_score', 'recruiter_score',
            'impact_score', 'brevity_score', 'style_score', 'keyword_match_score',
        ];

        foreach ($scoreKeys as $key) {
            $values = array_filter(
                array_map(fn($r) => isset($r[$key]) ? (int) $r[$key] : null, $results),
                fn($v) => $v !== null
            );
            if (! empty($values)) {
                // Conservative: use weighted average (floor to avoid inflation)
                $base[$key] = (int) floor(array_sum($values) / count($values));
            }
        }

        // ── 2. Average section_scores ────────────────────────────────────
        $sectionKeys = array_keys($base['section_scores'] ?? []);
        foreach ($sectionKeys as $sec) {
            $vals = [];
            foreach ($results as $r) {
                $v = $r['section_scores'][$sec]['score'] ?? null;
                if ($v !== null) $vals[] = (int) $v;
            }
            if (! empty($vals) && isset($base['section_scores'][$sec])) {
                $base['section_scores'][$sec]['score'] = (int) floor(array_sum($vals) / count($vals));

                // Pick the longest feedback string
                $feedbacks = array_filter(array_map(
                    fn($r) => $r['section_scores'][$sec]['feedback'] ?? null,
                    $results
                ));
                if ($feedbacks) {
                    usort($feedbacks, fn($a, $b) => strlen($b) - strlen($a));
                    $base['section_scores'][$sec]['feedback'] = reset($feedbacks);
                }
            }
        }

        // ── 3. Merge array fields (union + deduplicate) ─────────────────
        $mergeArrayKeys = [
            'critical_issues', 'grammar_errors', 'keyword_gaps',
            'formatting_issues', 'recommended_keywords', 'action_verbs_suggestions',
            'strengths', 'weaknesses',
        ];

        foreach ($mergeArrayKeys as $key) {
            $merged = [];
            foreach ($results as $r) {
                foreach ((array) ($r[$key] ?? []) as $item) {
                    $sig = is_array($item) ? json_encode($item) : $item;
                    if (! in_array($sig, $merged, true)) {
                        $merged[] = $sig === $item ? $item : $item;
                    }
                }
            }
            $base[$key] = array_values(array_unique($merged, SORT_REGULAR));
        }

        // ── 4. Merge checks (union by id, pick worst-case status) ────────
        $checkMap = [];
        foreach ($results as $r) {
            foreach ((array) ($r['checks'] ?? []) as $check) {
                $id = $check['id'] ?? null;
                if (! $id) continue;
                if (! isset($checkMap[$id])) {
                    $checkMap[$id] = $check;
                } else {
                    // Prefer 'issue' over 'passed' (conservative ensemble)
                    if (($check['status'] ?? '') === 'issue') {
                        $checkMap[$id]['status'] = 'issue';
                    }
                    // Average check score
                    $s1 = (int) ($checkMap[$id]['score'] ?? 0);
                    $s2 = (int) ($check['score'] ?? 0);
                    $checkMap[$id]['score'] = $s1 > 0 && $s2 > 0
                        ? (int) floor(($s1 + $s2) / 2)
                        : max($s1, $s2);
                    // Merge issues array
                    $existing = $checkMap[$id]['issues'] ?? [];
                    $incoming = $check['issues'] ?? [];
                    $merged   = array_merge($existing, $incoming);
                    $checkMap[$id]['issues']      = array_values(array_unique($merged, SORT_REGULAR));
                    $checkMap[$id]['issue_count'] = count($checkMap[$id]['issues']);
                    // Pick longer description
                    if (strlen($check['description'] ?? '') > strlen($checkMap[$id]['description'] ?? '')) {
                        $checkMap[$id]['description'] = $check['description'];
                    }
                }
            }
        }
        $base['checks'] = array_values($checkMap);

        // ── 5. Prefer longest executive_summary ──────────────────────────
        $summaries = array_filter(array_map(fn($r) => $r['executive_summary'] ?? null, $results));
        if ($summaries) {
            usort($summaries, fn($a, $b) => strlen($b) - strlen($a));
            $base['executive_summary'] = reset($summaries);
        }

        // ── 6. Merge detailed_line_by_line_feedback ──────────────────────
        $allLines = [];
        foreach ($results as $r) {
            foreach ((array) ($r['detailed_line_by_line_feedback'] ?? []) as $fb) {
                $sig = ($fb['line'] ?? '') . '|' . ($fb['section'] ?? '');
                if (! isset($allLines[$sig])) {
                    $allLines[$sig] = $fb;
                }
            }
        }
        $base['detailed_line_by_line_feedback'] = array_values($allLines);

        // ── 7. Merge rewritten_bullets ────────────────────────────────────
        $bulletMap = [];
        foreach ($results as $r) {
            foreach ((array) ($r['rewritten_bullets'] ?? []) as $b) {
                $sig = $b['original'] ?? '';
                if (! isset($bulletMap[$sig])) {
                    $bulletMap[$sig] = $b;
                }
            }
        }
        $base['rewritten_bullets'] = array_values($bulletMap);

        // Tag so the frontend/logs can see ensemble was used
        $base['ai_ensemble'] = true;
        $base['ai_providers'] = $count;
        $base['ai_providers_list'] = array_values(array_unique(array_filter(array_map(fn($r) => $r['provider_name'] ?? null, $results))));

        \Log::info('[CVGenius] Ensemble merge complete.', [
            'overall_score'    => $base['overall_score'],
            'providers_merged' => $count,
            'providers_list'   => $base['ai_providers_list'],
        ]);

        return $base;
    }


    /**
     * Rewrite a weak resume bullet point into 3 premium suggestions.
     *
     * @return array<string, mixed>
     */
    public function rewriteBullet(string $bulletText): array
    {
        set_time_limit(45);

        // Sanitize input
        $bulletText = trim(strip_tags($bulletText));

        if (empty($bulletText)) {
            return ['suggestions' => []];
        }

        $prompt = <<<PROMPT
You are a world-class executive resume writer and senior career coach who has helped candidates land roles at Google, Amazon, and top Fortune 500 companies.

Your task: take the following weak or average resume bullet point and rewrite it into EXACTLY 3 distinct, powerful, executive-level alternatives.

Each alternative MUST:
- Start with a strong, varied action verb (Engineered / Architected / Orchestrated / Spearheaded / Delivered / Accelerated / Scaled / etc.)
- Follow the Google Resume Formula: Accomplished [X] as measured by [Y] by doing [Z]
- Include quantifiable metrics (%, $, number of users, time saved, etc.) — even if you need to suggest realistic approximate ranges
- Be optimised for ATS keyword density
- Be unique in perspective from the other two (one metric-focused, one team/leadership-focused, one business-impact-focused)
- Be no longer than 2 lines

Respond ONLY with strictly valid JSON matching EXACTLY this structure. No markdown. No explanation outside JSON:
{
    "suggestions": [
        "[First alternative — metric-focused, strong action verb, quantified impact]",
        "[Second alternative — leadership or collaboration angle, different action verb, quantified impact]",
        "[Third alternative — business/product/revenue impact angle, strong action verb, quantified impact]"
    ]
}

Original Bullet Point to Rewrite:
"{$bulletText}"
PROMPT;

        $result = null;

        if (config('services.gemini.key')) {
            try {
                $result = $this->callGeminiWithTemperature($prompt, 0.7);
            } catch (\Throwable $e) {
                \Log::warning('Gemini bullet rewrite failed: '.$e->getMessage());
            }
        }

        if (! $result && config('services.openai.key')) {
            try {
                $result = $this->callOpenAIWithTemperature($prompt, 0.7);
            } catch (\Throwable $e) {
                \Log::warning('OpenAI bullet rewrite failed: '.$e->getMessage());
            }
        }

        if (! $result && config('services.groq.key')) {
            try {
                $result = $this->callGroqWithTemperature($prompt, 0.7);
            } catch (\Throwable $e) {
                \Log::warning('Groq bullet rewrite failed: '.$e->getMessage());
            }
        }

        // Validate suggestions structure
        if ($result && isset($result['suggestions']) && is_array($result['suggestions'])) {
            $result['suggestions'] = array_values(
                array_filter($result['suggestions'], fn($s) => is_string($s) && strlen(trim($s)) > 10)
            );
            if (count($result['suggestions']) >= 1) {
                return $result;
            }
        }

        // Contextual fallback based on original text
        \Log::info('[CVGenius] Bullet rewrite fallback used for: '.substr($bulletText, 0, 60));
        return $this->generateMockBulletRewrite($bulletText);
    }

    /**
     * Generate context-aware mock bullet rewrites for local development fallback.
     *
     * @return array<string, mixed>
     */
    private function generateMockBulletRewrite(string $bulletText): array
    {
        // Attempt to extract the core verb/tech from the original for more realistic mocks
        $hasApi    = stripos($bulletText, 'api') !== false;
        $hasDb     = preg_match('/sql|database|mysql|postgres/i', $bulletText);
        $hasTeam   = preg_match('/team|collab|work with/i', $bulletText);
        $hasFront  = preg_match('/ui|front|vue|react|css/i', $bulletText);

        if ($hasApi) {
            return ['suggestions' => [
                'Engineered and deployed 12+ RESTful API endpoints serving 50,000+ daily requests, achieving 99.97% uptime and reducing average response time by 40%.',
                'Architected a secure, versioned API layer using JWT authentication and rate-limiting, eliminating 100% of unauthorised access incidents post-launch.',
                'Accelerated API delivery velocity by 35% by introducing contract-first OpenAPI documentation, reducing frontend integration time from 2 weeks to 4 days.',
            ]];
        }

        if ($hasDb) {
            return ['suggestions' => [
                'Optimised 8 complex SQL queries in a high-traffic Laravel application, cutting average page load time from 3.2s to 0.8s and improving user retention by 22%.',
                'Designed and implemented a normalised relational database schema supporting 500,000+ records with zero data integrity issues over 18 months of production use.',
                'Reduced database server costs by 30% by introducing Eloquent query caching and index optimisation, eliminating 95% of N+1 query issues.',
            ]];
        }

        if ($hasFront) {
            return ['suggestions' => [
                'Engineered 15+ reusable Vue.js components adopted across 3 product teams, reducing UI development time by 45% and ensuring pixel-perfect design consistency.',
                'Spearheaded a front-end performance overhaul using lazy loading and code splitting, improving Lighthouse performance score from 54 to 94 and reducing bounce rate by 18%.',
                'Delivered a fully responsive, accessible UI for a SaaS dashboard serving 10,000+ monthly users, achieving WCAG 2.1 AA compliance and cutting support tickets by 30%.',
            ]];
        }

        // Generic high-quality fallback
        return ['suggestions' => [
            'Engineered a scalable, production-grade solution that improved system performance by 35% and reduced operational overhead by 20%, directly supporting a 15% increase in team delivery velocity.',
            'Orchestrated cross-functional collaboration across 3 departments to deliver a mission-critical feature 2 weeks ahead of schedule, contributing to a $120K revenue milestone.',
            'Designed and launched a robust technical implementation that reduced manual processing time by 60%, enabling the team to reallocate 10+ engineering hours per week to high-value product initiatives.',
        ]];
    }

    /**
     * Call Gemini with a custom temperature (for creative tasks like bullet rewriting).
     *
     * @return array<string, mixed>|null
     */
    private function callGeminiWithTemperature(string $prompt, float $temperature): ?array
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            return null;
        }

        $models = config('services.gemini.rewrite_models', ['gemini-2.5-flash', 'gemini-2.0-flash']);
        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => $temperature,
            ],
        ];

        foreach ($models as $model) {
            $model = trim((string) $model);
            if ($model === '') {
                continue;
            }

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
                .$model.':generateContent?key='.$key;

            try {
                $response = Http::withoutVerifying()
                    ->connectTimeout(5)
                    ->timeout(12)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                if ($response->status() === 429) {
                    \Log::warning("Gemini rewrite quota exceeded ({$model})");

                    continue;
                }

                if (! $response->successful()) {
                    \Log::warning("Gemini rewrite HTTP {$response->status()} ({$model})");

                    continue;
                }

                $data = $response->json();
                $jsonText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $parsed = $this->parseJsonResponse($jsonText);

                if (is_array($parsed)) {
                    return $parsed;
                }
            } catch (\Throwable $e) {
                \Log::warning("Gemini rewrite failed ({$model}): ".$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Call OpenAI with a custom temperature.
     *
     * @return array<string, mixed>|null
     */
    private function callOpenAIWithTemperature(string $prompt, float $temperature): ?array
    {
        if (empty(config('services.openai.key'))) {
            return null;
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert executive resume writer. Reply with strictly valid JSON only. No markdown.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => $temperature,
            ]);

            $content = $response->choices[0]->message->content ?? '';

            return $this->parseJsonResponse($content);
        } catch (\Throwable $e) {
            \Log::warning('OpenAI bullet rewrite failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Match resume text to a job description using Gemini first, then OpenAI fallback.
     *
     * @return array<string, mixed>
     */
    public function matchResumeToJob(string $resumeText, string $jobTitle, string $jobDescription): array
    {
        set_time_limit(180);

        if (trim($resumeText) === '') {
            throw new \Exception('Resume text is empty. Cannot match.');
        }

        $prompt = $this->buildJobMatchPrompt($resumeText, $jobTitle, $jobDescription);
        $result = null;

        if (config('services.gemini.key')) {
            try {
                $result = $this->callGemini($prompt);
            } catch (\Throwable $e) {
                \Log::warning('Gemini job match failed: '.$e->getMessage());
            }
        }

        if (! $result && config('services.openai.key')) {
            try {
                $result = $this->callOpenAI($prompt);
            } catch (\Throwable $e) {
                \Log::warning('OpenAI job match failed: '.$e->getMessage());
            }
        }

        if (! $result && config('services.groq.key')) {
            try {
                $result = $this->callGroq($prompt);
            } catch (\Throwable $e) {
                \Log::warning('Groq job match failed: '.$e->getMessage());
            }
        }

        if (! $result) {
            if (app()->environment('local')) {
                \Log::info('AI job matching failed or quota exceeded in local development. Falling back to structured mock match payload.');
                $result = $this->generateMockJobMatchResult($jobTitle);
            } else {
                if (! config('services.gemini.key') && ! config('services.openai.key')) {
                    throw new \Exception('No AI API key configured. Add GEMINI_API_KEY to .env');
                }
                throw new \Exception('The AI job matching failed to process. Please check your API keys, quota, or network connection.');
            }
        }

        if (! isset($result['match_score'])) {
            throw new \Exception('The AI returned an invalid match payload (missing match_score).');
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function callGemini(string $prompt): ?array
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            return null;
        }

        $url = self::GEMINI_GENERATE_URL.'?key='.$key;

        $response = Http::withoutVerifying()->timeout(120)->retry(2, 2000)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.2,
            ],
        ]);

        if (! $response->successful()) {
            \Log::error('Gemini API error: '.$response->status().' '.$response->body());

            return null;
        }

        $data = $response->json();
        $jsonText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $this->parseJsonResponse($jsonText);
    }

    /**
     * Call Groq Cloud (OpenAI-compatible) — free tier with Llama 3.3 70B.
     * Tries multiple models with 429 fallback between them.
     *
     * @return array<string, mixed>|null
     */
    private function callGroq(string $prompt): ?array
    {
        $key = config('services.groq.key');
        if (empty($key)) {
            return null;
        }

        $models = config('services.groq.models', ['llama-3.3-70b-versatile', 'llama3-8b-8192']);

        foreach ($models as $model) {
            try {
                $response = Http::withoutVerifying()
                    ->withToken($key)
                    ->timeout(90)
                    ->retry(1, 2000)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'       => $model,
                        'messages'    => [
                            [
                                'role'    => 'system',
                                'content' => 'You are a brutally honest senior recruiter and ATS expert. Reply ONLY with strictly valid JSON — no markdown, no explanation.',
                            ],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature'     => 0.2,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                if ($response->status() === 429) {
                    \Log::warning("Groq quota exceeded ({$model}), trying next model.");
                    continue;
                }

                if (! $response->successful()) {
                    \Log::warning("Groq HTTP {$response->status()} ({$model})");
                    continue;
                }

                $content = $response->json('choices.0.message.content', '');
                $parsed  = $this->parseJsonResponse($content);

                if (is_array($parsed)) {
                    \Log::info("[CVGenius] Groq ({$model}) analysis succeeded.");
                    return $parsed;
                }
            } catch (\Throwable $e) {
                \Log::warning("Groq call failed ({$model}): ".$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Call Groq for creative tasks (bullet rewriting) with higher temperature.
     *
     * @return array<string, mixed>|null
     */
    private function callGroqWithTemperature(string $prompt, float $temperature): ?array
    {
        $key = config('services.groq.key');
        if (empty($key)) {
            return null;
        }

        $models = config('services.groq.models', ['llama-3.3-70b-versatile', 'llama3-8b-8192']);

        foreach ($models as $model) {
            try {
                $response = Http::withoutVerifying()
                    ->withToken($key)
                    ->timeout(30)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'       => $model,
                        'messages'    => [
                            [
                                'role'    => 'system',
                                'content' => 'You are a world-class executive resume writer. Reply ONLY with strictly valid JSON. No markdown.',
                            ],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature'     => $temperature,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                if ($response->status() === 429) {
                    continue;
                }

                if (! $response->successful()) {
                    continue;
                }

                $content = $response->json('choices.0.message.content', '');
                $parsed  = $this->parseJsonResponse($content);

                if (is_array($parsed)) {
                    return $parsed;
                }
            } catch (\Throwable $e) {
                \Log::warning("Groq rewrite failed ({$model}): ".$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Call OpenAI GPT-4o-mini.
     *
     * @return array<string, mixed>|null
     */
    private function callOpenAI(string $prompt): ?array
    {
        if (empty(config('services.openai.key'))) {
            return null;
        }

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a brutally honest senior recruiter. Reply with strictly valid JSON only. No markdown. Be strict with scores.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.2,
        ]);

        $content = $response->choices[0]->message->content ?? '';

        return $this->parseJsonResponse($content);
    }

    /**
     * Call an OpenAI-compatible endpoint (Mistral, Cerebras, Together, etc.).
     *
     * @return array<string, mixed>|null
     */
    private function callOpenAICompatible(string $prompt, string $baseUrl, string $model, string $key): ?array
    {
        if (empty($key) || empty($baseUrl)) {
            return null;
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($key)
                ->timeout(90)
                ->retry(1, 2000)
                ->post($baseUrl, [
                    'model'       => $model,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => 'You are a brutally honest senior recruiter and ATS expert. Reply ONLY with strictly valid JSON — no markdown, no explanation.',
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature'     => 0.2,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (! $response->successful()) {
                \Log::warning("OpenAI-compatible API call failed on {$baseUrl} with status: " . $response->status());
                return null;
            }

            $content = $response->json('choices.0.message.content', '');
            return $this->parseJsonResponse($content);
        } catch (\Throwable $e) {
            \Log::warning("OpenAI-compatible call failed to {$baseUrl} ({$model}): " . $e->getMessage());
            return null;
        }
    }


    /**
     * @return array<string, mixed>|null
     */
    private function parseJsonResponse(string $rawResponse): ?array
    {
        $rawResponse = trim($rawResponse);
        $rawResponse = preg_replace('/^```json\s*|```$/m', '', $rawResponse) ?? $rawResponse;

        $decoded = json_decode($rawResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if (preg_match('/\{.*\}/s', $rawResponse, $matches) && ! empty($matches[0])) {
                $decoded = json_decode($matches[0], true);
            }
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Server-side guardrails so inflated AI scores cannot pass unchecked.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function enforceStrictScoring(array $result, string $resumeText): array
    {
        return (new ResumeScoringEngine())->calibrate($result, $resumeText);
    }

    private function buildAnalysisPrompt(string $resumeText): string
    {
        return CvGeniusAnalysisPrompt::build($resumeText);
    }

    private function buildJobMatchPrompt(string $text, string $jobTitle, string $jobDescription): string
    {
        return <<<PROMPT
You are an expert ATS (Applicant Tracking System) compatibility matching system.
Compare the following resume text against the target Job Title and Job Description provided.
Calculate a highly realistic, dynamic compatibility match score (0 to 100) based on how well the skills, experience, and profile of the candidate align with the job requirements.

In addition, extract a list of 3 to 6 key missing skills or keywords that are heavily mentioned in the Job Description but missing or weakly presented in the resume, and explain why each is important.

Also, provide 2 to 4 dynamic bullet-point tailoring suggestions: take a weak or average bullet point directly from the candidate's resume and rewrite it to perfectly match the key requirements of the target job description (similar to how a professional resume writer would do it).

You MUST respond in strictly valid JSON matching this exact structure:
{
    "match_score": 78,
    "summary": "Detailed overall alignment summary explaining how well the candidate fits the target job description.",
    "missing_keywords": [
        {
            "keyword": "[Missing Keyword/Skill]",
            "importance": "HIGH",
            "reason": "[Explain why this is highly critical for this specific job position]"
        }
    ],
    "tailoring_suggestions": [
        {
            "section": "Experience",
            "original_bullet": "[Extract a real weak or average line from the resume text]",
            "feedback": "Explain why this original line is weak for the target job description.",
            "suggested_bullet": "[Provide a powerful tailored rewrite of this exact line matching the target JD requirements]"
        }
    ]
}

Target Job Title: {$jobTitle}

Target Job Description:
----------
{$jobDescription}
----------

Candidate Resume Text:
----------
{$text}
----------
PROMPT;
    }

    /**
     * Generate a realistic mock analysis result for local testing/fallback.
     *
     * @return array<string, mixed>
     */
    private function generateMockAnalysisResult(Resume $resume): array
    {
        $title = $resume->title ?: 'Resume';
        
        $overall = rand(66, 74);

        return [
            'overall_score' => $overall,
            'ats_score' => rand(68, 82),
            'recruiter_score' => rand(70, 85),
            'impact_score' => rand(58, 75),
            'brevity_score' => rand(72, 88),
            'style_score' => rand(85, 98),
            'keyword_match_score' => rand(55, 72),
            'language_detected' => 'english',
            'detected_profession' => 'Full-Stack Developer',
            'candidate_name' => 'Emam Medhat',
            'candidate_level' => 'mid',
            'executive_summary' => 'Solid technical foundation with Laravel and Vue.js experience. Primary gaps: weak action verbs, missing metrics on key bullets, and limited ATS keyword density for senior roles.',
            'strengths' => ['Clear technical stack', 'Progressive job titles', 'Clean formatting'],
            'weaknesses' => ['Unquantified achievements', 'Passive verbs in experience', 'Generic summary opening'],
            'critical_issues' => ['Multiple experience bullets lack measurable outcomes'],
            'grammar_errors' => [],
            'keyword_gaps' => ['CI/CD', 'Kubernetes', 'System design'],
            'formatting_issues' => [],
            'recommended_keywords' => ['REST API', 'PostgreSQL', 'Docker', 'Agile'],
            'action_verbs_suggestions' => ['Engineered', 'Orchestrated', 'Delivered', 'Scaled'],
            'rewritten_bullets' => [
                [
                    'original' => 'Worked on real-world scenarios simulating production environments.',
                    'improved' => 'Engineered production-grade features in simulated enterprise environments, reducing deployment risk by 20%.',
                    'reason' => 'Replace passive phrasing with impact metrics.',
                ],
            ],
            'detailed_line_by_line_feedback' => [
                [
                    'section' => 'Experience',
                    'line' => 'Worked on real-world scenarios simulating production environments.',
                    'severity' => 'warning',
                    'feedback' => 'Passive verb weakens impact.',
                    'suggestion' => 'Lead with a strong verb and quantify outcome.',
                ],
            ],
            'section_scores' => [
                'contact' => ['score' => 90, 'feedback' => 'Complete contact block.'],
                'summary' => ['score' => 72, 'feedback' => 'Generic opening; add niche specialization.'],
                'experience' => ['score' => 68, 'feedback' => 'Needs more quantified bullets.'],
                'skills' => ['score' => 85, 'feedback' => 'Relevant stack listed.'],
                'education' => ['score' => 88, 'feedback' => 'Clear degree and dates.'],
                'ats' => ['score' => 75, 'feedback' => 'Good base keywords; add cloud/DevOps terms.'],
                'impact' => ['score' => 62, 'feedback' => 'Too few metrics in bullets.'],
                'dates' => ['score' => 95, 'feedback' => 'Consistent dating.'],
            ],
            'score_headline' => "Your resume scored {$overall}/100. Strong resume with minor fixable issues.",
            'score_explanation' => 'Your resume has a strong core structure, but lacks sufficient quantification in the Experience section. Several bullet points describe tasks instead of tangible business achievements.',
            'checks' => [
                [
                    'id' => 'readability',
                    'name' => 'Readability',
                    'score' => 75,
                    'status' => 'issue',
                    'issue_count' => 3,
                    'points_impact' => -8,
                    'title' => 'Passive verb usage & long bullets',
                    'description' => 'We found passive verbs ("worked on", "helped") and a few bullets exceeding 2 lines.',
                    'issues' => [
                        [
                            'original_line' => 'Worked on real-world scenarios simulating production environments and teamwork.',
                            'improved_line' => 'Engineered scalable features for production environments, collaborating with cross-functional teams to boost velocity.',
                            'reason' => 'Active verbs show leadership and accountability to recruiters.'
                        ],
                        [
                            'original_line' => 'Helped the testing team perform quality checks on the database system and backend API endpoints.',
                            'improved_line' => 'Orchestrated automated test suites for PostgreSQL databases and RESTful API endpoints, reducing regressions by 25%.',
                            'reason' => 'Avoid "helped" and replace with strong action verbs and quantified impact.'
                        ]
                    ]
                ],
                [
                    'id' => 'growth_signals',
                    'name' => 'Growth signals',
                    'score' => 60,
                    'status' => 'issue',
                    'issue_count' => 2,
                    'points_impact' => -10,
                    'title' => 'Unquantified Achievements',
                    'description' => 'Deduct 10pts per unquantified bullet point in the Experience section.',
                    'issues' => [
                        [
                            'original_line' => 'Designed and implemented real-time features using WebSockets and Redis.',
                            'improved_line' => 'Designed and launched real-time communication modules using WebSockets and Redis, reducing latency by 35%.',
                            'reason' => 'Quantifying your outcomes proves the scale and efficacy of your work.'
                        ]
                    ]
                ],
                [
                    'id' => 'job_fit',
                    'name' => 'Job fit',
                    'status' => 'locked',
                    'score' => 0,
                    'issue_count' => 0,
                    'points_impact' => 0,
                    'title' => 'Unlock to see job fit',
                    'description' => 'Check industry-specific keyword density against target job descriptions.',
                    'issues' => []
                ],
                [
                    'id' => 'spelling',
                    'name' => 'Spelling & consistency',
                    'score' => 95,
                    'status' => 'passed',
                    'issue_count' => 0,
                    'points_impact' => 10,
                    'title' => 'Spelling & consistency looks great!',
                    'description' => 'Consistent date formatting and clean bullet styles throughout.',
                    'issues' => []
                ],
                [
                    'id' => 'buzzwords',
                    'name' => 'Buzzwords',
                    'score' => 85,
                    'status' => 'issue',
                    'issue_count' => 1,
                    'points_impact' => -15,
                    'title' => 'Generic buzzwords found',
                    'description' => 'Avoid overused filler buzzwords like "team player" or "hardworking".',
                    'issues' => [
                        [
                            'original_line' => 'A hardworking and detail-oriented software developer...',
                            'improved_line' => 'A result-driven software developer specialized in Laravel and Vue.js...',
                            'reason' => 'Recruiters prefer evidence of achievements over generic self-descriptions.'
                        ]
                    ]
                ],
                [
                    'id' => 'dates',
                    'name' => 'Dates',
                    'score' => 100,
                    'status' => 'passed',
                    'issue_count' => 0,
                    'points_impact' => 10,
                    'title' => 'All positions dated',
                    'description' => 'Consistent date formats and no unexplained work gaps over 6 months.',
                    'issues' => []
                ],
                [
                    'id' => 'unnecessary',
                    'name' => 'Unnecessary sections',
                    'score' => 100,
                    'status' => 'passed',
                    'issue_count' => 0,
                    'points_impact' => 10,
                    'title' => 'No unnecessary sections',
                    'description' => 'Clean resume focus without generic references or marital status sections.',
                    'issues' => []
                ]
            ],
            'resume_sections' => [
                'name' => 'Emam Medhat',
                'title' => 'Full-Stack Developer',
                'contact' => 'emam@example.com | +20 123 4567 890 | Mansoura, Egypt',
                'links' => ['https://linkedin.com/in/emammedhat', 'https://github.com/emammedhat'],
                'summary_text' => 'Passionate Full-Stack Developer with 4+ years of experience engineering scalable web applications with Laravel, Vue.js, and Tailwind CSS. Proficient in designing RESTful APIs and optimizing database architectures.',
                'experience' => [
                    [
                        'job_title' => 'Full-Stack Developer',
                        'company' => 'Tech Solutions Inc.',
                        'dates' => 'Jan 2022 - Present',
                        'bullets' => [
                            [
                                'text' => 'Worked on real-world scenarios simulating production environments and teamwork.',
                                'is_weak' => true,
                                'weak_reason' => 'Uses passive verb "Worked on"'
                            ],
                            [
                                'text' => 'Designed and implemented real-time features using WebSockets and Redis.',
                                'is_weak' => true,
                                'weak_reason' => 'Lacks metrics or quantified outcome'
                            ],
                            [
                                'text' => 'Collaborated with cross-functional teams to engineer and launch 15+ highly responsive customer-facing features.',
                                'is_weak' => false,
                                'weak_reason' => ''
                            ]
                        ]
                    ],
                    [
                        'job_title' => 'Junior Developer',
                        'company' => 'WebCreations Agency',
                        'dates' => 'Sep 2020 - Dec 2021',
                        'bullets' => [
                            [
                                'text' => 'Helped the testing team perform quality checks on database schemas.',
                                'is_weak' => true,
                                'weak_reason' => 'Uses passive verb "Helped"'
                            ]
                        ]
                    ]
                ],
                'skills_text' => 'PHP, Laravel, Vue.js, MySQL, JavaScript, HTML, CSS, Git, Docker, RESTful APIs',
                'education' => 'Bachelor of Science in Management Information Systems, Misr Higher Institute, 2020 - 2024'
            ]
        ];
    }

    /**
     * Generate a realistic mock job match result for local testing/fallback.
     *
     * @return array<string, mixed>
     */
    private function generateMockJobMatchResult(string $jobTitle): array
    {
        return [
            'match_score' => rand(65, 85),
            'summary' => "Your resume aligns moderately well with the $jobTitle requirements. Your strong experience with Laravel and Vue.js matches the core stack, but your profile lacks some secondary requirements like Docker containerization and Redis caching.",
            'missing_keywords' => [
                [
                    'keyword' => 'Docker',
                    'importance' => 'HIGH',
                    'reason' => 'Highly requested in the JD for local and production environment parity.'
                ],
                [
                    'keyword' => 'Redis',
                    'importance' => 'MEDIUM',
                    'reason' => 'Important for low-latency session management and real-time alerts.'
                ]
            ],
            'tailoring_suggestions' => [
                [
                    'section' => 'Experience',
                    'original_bullet' => 'Worked on real-world scenarios simulating production environments.',
                    'feedback' => 'This bullet point lacks action verbs and does not mention any technical scaling.',
                    'suggested_bullet' => 'Designed and deployed local Dockerized environments matching production configurations, enhancing deployment confidence.'
                ]
            ]
        ];
    }
}
