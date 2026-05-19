<?php

namespace App\Support;

use App\Models\AnalysisReport;

class ResumePreviewBuilder
{
    private const WEAK_VERBS = [
        'worked on', 'helped', 'assisted', 'handled', 'responsible for',
        'involved in', 'participated in', 'contributed to', 'supported',
    ];

    private const SECTION_HEADERS = '/^(SUMMARY|PROFESSIONAL SUMMARY|PROFILE|OBJECTIVE|EXPERIENCE|WORK EXPERIENCE|PROFESSIONAL EXPERIENCE|EMPLOYMENT|EDUCATION|SKILLS|TECHNICAL SKILLS|PROJECTS|LANGUAGES|CERTIFICATIONS|ملخص|الخبرات|الخبرة|التعليم|المهارات)/iu';

    /**
     * Build full CV preview + annotate weak lines from AI checks / recommendations.
     *
     * @param  array<string, mixed>|null  $analysis
     * @return array<string, mixed>
     */
    public function build(?string $parsedText, ?array $analysis, ?AnalysisReport $report = null): array
    {
        $parsedText = trim((string) $parsedText);
        $issueLines = $this->collectIssueLines($analysis);

        if ($parsedText === '') {
            return $this->fromAiOnly($analysis, $issueLines);
        }

        $structured = $this->parseParsedText($parsedText);
        $structured = $this->annotateBullets($structured, $issueLines);
        $structured['full_text'] = $parsedText;
        $structured['projects_text'] = $structured['projects_text'] ?? $this->extractBlock($parsedText, 'projects');
        $structured['languages_text'] = $structured['languages_text'] ?? $this->extractBlock($parsedText, 'languages');
        $structured['certifications_text'] = $structured['certifications_text'] ?? $this->extractBlock($parsedText, 'certifications');

        // Merge AI resume_sections if richer (more bullets)
        if (is_array($analysis['resume_sections'] ?? null)) {
            $structured = $this->mergeAiSections($structured, $analysis['resume_sections'], $issueLines);
        }

        return $structured;
    }

    /**
     * Ensure analysis has checks array for sidebar / middle panel.
     *
     * @param  array<string, mixed>  $analysis
     * @return array<string, mixed>
     */
    public function ensureChecks(array $analysis, ?AnalysisReport $report = null): array
    {
        if (! empty($analysis['checks']) && is_array($analysis['checks'])) {
            return $analysis;
        }

        $analysis['checks'] = $this->buildChecksFromLegacy($analysis, $report);

        return $analysis;
    }

    /**
     * @param  array<string, mixed>|null  $analysis
     * @return list<array{line: string, reason: string, improved: string}>
     */
    private function collectIssueLines(?array $analysis): array
    {
        $collected = [];

        if (! is_array($analysis)) {
            return $collected;
        }

        foreach ($analysis['checks'] ?? [] as $check) {
            if (! is_array($check)) {
                continue;
            }
            foreach ($check['issues'] ?? [] as $issue) {
                if (! is_array($issue)) {
                    continue;
                }
                $line = trim((string) ($issue['original_line'] ?? $issue['original_text'] ?? ''));
                if ($line !== '') {
                    $collected[] = [
                        'line' => $line,
                        'reason' => (string) ($issue['reason'] ?? $issue['feedback'] ?? 'Needs improvement'),
                        'improved' => (string) ($issue['improved_line'] ?? $issue['draft_improvement'] ?? $issue['suggested_bullet'] ?? ''),
                    ];
                }
            }
        }

        foreach ($analysis['top_recommendations'] ?? [] as $rec) {
            if (! is_array($rec)) {
                continue;
            }
            $line = trim((string) ($rec['original_line'] ?? $rec['original_text'] ?? ''));
            if ($line !== '') {
                $collected[] = [
                    'line' => $line,
                    'reason' => (string) ($rec['reason'] ?? $rec['issue'] ?? 'Improve this line'),
                    'improved' => (string) ($rec['improved_line'] ?? $rec['draft_improvement'] ?? ''),
                ];
            }
        }

        return $collected;
    }

    /**
     * @param  array<string, mixed>  $sections
     * @param  list<array{line: string, reason: string, improved: string}>  $issueLines
     * @return array<string, mixed>
     */
    private function annotateBullets(array $sections, array $issueLines): array
    {
        if (empty($sections['experience']) || ! is_array($sections['experience'])) {
            return $sections;
        }

        foreach ($sections['experience'] as &$job) {
            if (empty($job['bullets']) || ! is_array($job['bullets'])) {
                continue;
            }
            foreach ($job['bullets'] as &$bullet) {
                $text = is_array($bullet) ? ($bullet['text'] ?? '') : (string) $bullet;
                $text = trim($text);
                if ($text === '') {
                    continue;
                }

                $match = $this->matchIssue($text, $issueLines);
                if ($match) {
                    $bullet = [
                        'text' => $text,
                        'is_weak' => true,
                        'weak_reason' => $match['reason'],
                        'improved_line' => $match['improved'] ?: null,
                    ];
                    continue;
                }

                $weakReason = $this->detectWeakVerb($text);
                $bullet = [
                    'text' => $text,
                    'is_weak' => $weakReason !== null,
                    'weak_reason' => $weakReason ?? '',
                    'improved_line' => null,
                ];
            }
            unset($bullet);
        }
        unset($job);

        return $sections;
    }

    /**
     * @param  list<array{line: string, reason: string, improved: string}>  $issueLines
     * @return array{reason: string, improved: string}|null
     */
    private function matchIssue(string $bulletText, array $issueLines): ?array
    {
        $normalizedBullet = $this->normalizeForMatch($bulletText);

        foreach ($issueLines as $issue) {
            $normalizedIssue = $this->normalizeForMatch($issue['line']);
            if ($normalizedIssue === '') {
                continue;
            }
            if (
                str_contains($normalizedBullet, $normalizedIssue)
                || str_contains($normalizedIssue, $normalizedBullet)
                || similar_text($normalizedBullet, $normalizedIssue) / max(strlen($normalizedBullet), 1) > 0.55
            ) {
                return ['reason' => $issue['reason'], 'improved' => $issue['improved']];
            }
        }

        return null;
    }

    private function normalizeForMatch(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function detectWeakVerb(string $text): ?string
    {
        foreach (self::WEAK_VERBS as $verb) {
            if (preg_match('/\b'.preg_quote($verb, '/').'\b/i', $text)) {
                return 'Uses weak phrasing: "'.$verb.'"';
            }
        }
        if (! preg_match('/\d|%|\$|k\b|m\b/i', $text)) {
            return 'Missing quantified result (numbers, %, or $)';
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseParsedText(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        $name = $lines[0] ?? 'Candidate';
        $title = $lines[1] ?? '';
        $contact = $lines[2] ?? '';

        $links = [];
        foreach ($lines as $line) {
            if (preg_match('/https?:\/\/\S+/i', $line, $m)) {
                $links[] = $m[0];
            }
        }

        $sections = [
            'summary' => [],
            'experience' => [],
            'skills' => [],
            'education' => [],
            'projects' => [],
            'languages' => [],
            'certifications' => [],
            'other' => [],
        ];

        $current = 'other';
        $experienceJobs = [];
        $jobIndex = -1;

        foreach ($lines as $i => $line) {
            if ($i < 3 && $current === 'other') {
                continue;
            }

            if (preg_match(self::SECTION_HEADERS, $line)) {
                $lower = mb_strtolower($line);
                $current = match (true) {
                    str_contains($lower, 'experience') || str_contains($lower, 'employment') || str_contains($lower, 'خبر') => 'experience',
                    str_contains($lower, 'education') || str_contains($lower, 'تعليم') => 'education',
                    str_contains($lower, 'skill') || str_contains($lower, 'مهار') => 'skills',
                    str_contains($lower, 'project') => 'projects',
                    str_contains($lower, 'language') || str_contains($lower, 'لغ') => 'languages',
                    str_contains($lower, 'certif') => 'certifications',
                    str_contains($lower, 'summary') || str_contains($lower, 'profile') || str_contains($lower, 'objective') || str_contains($lower, 'ملخص') => 'summary',
                    default => 'other',
                };
                $jobIndex = -1;
                continue;
            }

            $isBullet = (bool) preg_match('/^[•\-\*\x{2022}▪]\s*/u', $line)
                || (bool) preg_match('/^\d+[\.\)]\s+/', $line);

            if ($current === 'experience') {
                if ($isBullet) {
                    $bulletText = trim(preg_replace('/^[•\-\*\x{2022}▪\d\.\)]\s*/u', '', $line) ?? $line);
                    if ($jobIndex < 0) {
                        $experienceJobs[] = [
                            'job_title' => 'Experience',
                            'company' => '',
                            'dates' => '',
                            'bullets' => [],
                        ];
                        $jobIndex = count($experienceJobs) - 1;
                    }
                    $experienceJobs[$jobIndex]['bullets'][] = ['text' => $bulletText, 'is_weak' => false, 'weak_reason' => ''];
                } elseif ($this->looksLikeDateRange($line)) {
                    if ($jobIndex >= 0) {
                        $experienceJobs[$jobIndex]['dates'] = $line;
                    }
                } elseif ($jobIndex < 0 || count($experienceJobs[$jobIndex]['bullets'] ?? []) > 0) {
                    $experienceJobs[] = [
                        'job_title' => $line,
                        'company' => '',
                        'dates' => '',
                        'bullets' => [],
                    ];
                    $jobIndex = count($experienceJobs) - 1;
                } elseif ($jobIndex >= 0) {
                    $experienceJobs[$jobIndex]['company'] = $line;
                }
                continue;
            }

            $sections[$current][] = $isBullet
                ? trim(preg_replace('/^[•\-\*\x{2022}▪\d\.\)]\s*/u', '', $line) ?? $line)
                : $line;
        }

        $sections['experience'] = $experienceJobs;

        return [
            'name' => $name,
            'title' => $title,
            'contact' => $contact,
            'links' => array_values(array_unique($links)),
            'summary_text' => implode(' ', $sections['summary']),
            'experience' => $sections['experience'],
            'skills_text' => implode(' · ', $sections['skills']),
            'education' => implode("\n", $sections['education']),
            'projects_text' => implode("\n", $sections['projects']),
            'languages_text' => implode("\n", $sections['languages']),
            'certifications_text' => implode("\n", $sections['certifications']),
            'other_lines' => $sections['other'],
        ];
    }

    private function looksLikeDateRange(string $line): bool
    {
        return (bool) preg_match('/\b(19|20)\d{2}\b|present|current|حتى|الآن|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec/i', $line);
    }

    private function extractBlock(string $text, string $section): string
    {
        $pattern = match ($section) {
            'projects' => '/^(PROJECTS?)/im',
            'languages' => '/^(LANGUAGES?)/im',
            'certifications' => '/^(CERTIFICATIONS?)/im',
            default => null,
        };
        if (! $pattern) {
            return '';
        }

        if (! preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $start = $m[0][1];
        $rest = substr($text, $start);
        $end = preg_match(self::SECTION_HEADERS, $rest, $m2, PREG_OFFSET_CAPTURE, 20)
            ? ($m2[0][1] ?? strlen($rest))
            : strlen($rest);

        return trim(substr($rest, 0, max(0, $end)));
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $ai
     * @param  list<array{line: string, reason: string, improved: string}>  $issueLines
     * @return array<string, mixed>
     */
    private function mergeAiSections(array $base, array $ai, array $issueLines): array
    {
        $baseBulletCount = collect($base['experience'] ?? [])->sum(fn ($j) => count($j['bullets'] ?? []));
        $aiBulletCount = collect($ai['experience'] ?? [])->sum(fn ($j) => count($j['bullets'] ?? []));

        $merged = $aiBulletCount >= $baseBulletCount ? $ai : $base;

        foreach (['name', 'title', 'contact', 'links', 'summary_text', 'skills_text', 'education'] as $key) {
            if (empty($merged[$key]) && ! empty($base[$key])) {
                $merged[$key] = $base[$key];
            }
            if (empty($merged[$key]) && ! empty($ai[$key])) {
                $merged[$key] = $ai[$key];
            }
        }

        $merged['full_text'] = $base['full_text'] ?? '';

        return $this->annotateBullets($merged, $issueLines);
    }

    /**
     * @param  list<array{line: string, reason: string, improved: string}>  $issueLines
     * @return array<string, mixed>
     */
    private function fromAiOnly(?array $analysis, array $issueLines): array
    {
        $sections = is_array($analysis['resume_sections'] ?? null)
            ? $analysis['resume_sections']
            : [
                'name' => $analysis['candidate_name'] ?? 'Candidate',
                'title' => '',
                'contact' => '',
                'links' => [],
                'summary_text' => '',
                'experience' => [],
                'skills_text' => '',
                'education' => '',
            ];

        return $this->annotateBullets($sections, $issueLines);
    }

    /**
     * @param  array<string, mixed>  $analysis
     * @return list<array<string, mixed>>
     */
    private function buildChecksFromLegacy(array $analysis, ?AnalysisReport $report): array
    {
        $impact = is_array($report?->impact_score)
            ? (int) ($report->impact_score['score'] ?? 60)
            : (int) ($analysis['impact_score'] ?? 60);
        $brevity = is_array($report?->brevity_score)
            ? (int) ($report->brevity_score['score'] ?? 60)
            : (int) ($analysis['brevity_score'] ?? 60);
        $style = is_array($report?->style_score)
            ? (int) ($report->style_score['score'] ?? 60)
            : (int) ($analysis['style_score'] ?? 60);

        $recIssues = collect($analysis['top_recommendations'] ?? [])
            ->map(fn ($r) => [
                'original_line' => $r['original_line'] ?? $r['original_text'] ?? '',
                'improved_line' => $r['improved_line'] ?? $r['draft_improvement'] ?? '',
                'reason' => $r['reason'] ?? $r['issue'] ?? '',
            ])
            ->filter(fn ($i) => trim((string) $i['original_line']) !== '')
            ->values()
            ->all();

        return [
            [
                'id' => 'readability',
                'name' => 'Readability',
                'score' => $brevity,
                'status' => $brevity >= 75 ? 'passed' : 'issue',
                'issue_count' => $brevity >= 75 ? 0 : max(1, count($recIssues)),
                'points_impact' => $brevity >= 75 ? 10 : -10,
                'title' => $brevity >= 75 ? 'Readability looks good' : 'Readability needs work',
                'description' => $analysis['summary_feedback'] ?? 'Improve clarity and action verbs.',
                'issues' => array_slice($recIssues, 0, 5),
            ],
            [
                'id' => 'growth_signals',
                'name' => 'Growth signals',
                'score' => $impact,
                'status' => $impact >= 75 ? 'passed' : 'issue',
                'issue_count' => $impact >= 75 ? 0 : 2,
                'points_impact' => $impact >= 75 ? 10 : -15,
                'title' => $impact >= 75 ? 'Strong impact signals' : 'Add measurable achievements',
                'description' => 'Quantify results in experience bullets.',
                'issues' => [],
            ],
            [
                'id' => 'job_fit',
                'name' => 'Job fit',
                'status' => 'passed',
                'score' => 100,
                'issue_count' => 0,
                'points_impact' => 10,
                'title' => 'Target Job Fit Matcher',
                'description' => 'Match and compare your resume against target job descriptions to optimize for applicant tracking systems.',
                'issues' => [],
            ],
            [
                'id' => 'spelling',
                'name' => 'Spelling & consistency',
                'score' => $style,
                'status' => $style >= 75 ? 'passed' : 'issue',
                'issue_count' => $style >= 75 ? 0 : 1,
                'points_impact' => $style >= 75 ? 10 : -5,
                'title' => 'Formatting & consistency',
                'description' => '',
                'issues' => [],
            ],
        ];
    }
}
