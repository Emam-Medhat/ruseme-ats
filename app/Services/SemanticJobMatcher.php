<?php

namespace App\Services;

class SemanticJobMatcher
{
    private const TECH_KEYWORDS = [
        'docker', 'kubernetes', 'aws', 'azure', 'gcp', 'redis', 'postgresql', 'mysql',
        'ci/cd', 'cicd', 'microservices', 'rest', 'api', 'graphql', 'agile', 'scrum',
        'laravel', 'vue', 'react', 'node', 'python', 'java', 'typescript', 'javascript',
        'terraform', 'linux', 'git', 'kafka', 'rabbitmq', 'elasticsearch', 'mongodb',
    ];

    public function __construct(
        protected ResumeAnalysisService $analysisService
    ) {}

    /**
     * Deep semantic job match: AI + keyword overlap + ATS simulation.
     *
     * @return array<string, mixed>
     */
    public function match(string $resumeText, string $jobTitle, string $jobDescription): array
    {
        $jdKeywords = $this->extractKeywords($jobDescription);
        $resumeKeywords = $this->extractKeywords($resumeText);

        $overlap = array_values(array_intersect(
            array_map('mb_strtolower', $jdKeywords),
            array_map('mb_strtolower', $resumeKeywords)
        ));

        $keywordCoverage = count($jdKeywords) > 0
            ? (int) round((count($overlap) / count($jdKeywords)) * 100)
            : 0;

        $aiResult = $this->analysisService->matchResumeToJob($resumeText, $jobTitle, $jobDescription);

        $aiScore = (int) ($aiResult['match_score'] ?? 0);
        $semanticScore = (int) round($aiScore * 0.72 + $keywordCoverage * 0.28);
        $semanticScore = max(0, min(100, $semanticScore));

        $missingFromJd = array_values(array_diff(
            array_map('mb_strtolower', $jdKeywords),
            array_map('mb_strtolower', $resumeKeywords)
        ));

        $missingSkills = $this->mergeMissingSkills(
            $aiResult['missing_keywords'] ?? [],
            $missingFromJd
        );

        $keywordGaps = $aiResult['keyword_gaps'] ?? array_slice($missingFromJd, 0, 8);

        $recommendations = $this->buildRecommendations(
            $aiResult['tailoring_suggestions'] ?? [],
            $missingSkills,
            $keywordGaps
        );

        return [
            'matchScore' => $semanticScore,
            'match_score' => $semanticScore,
            'summary' => $aiResult['summary'] ?? '',
            'missingSkills' => $missingSkills,
            'missing_keywords' => $aiResult['missing_keywords'] ?? [],
            'keywordGaps' => array_values(array_slice($keywordGaps, 0, 10)),
            'keyword_gaps' => array_values(array_slice($keywordGaps, 0, 10)),
            'recommendations' => $recommendations,
            'tailoring_suggestions' => $aiResult['tailoring_suggestions'] ?? [],
            'semantic_meta' => [
                'ai_score' => $aiScore,
                'keyword_coverage' => $keywordCoverage,
                'jd_keyword_count' => count($jdKeywords),
                'overlap_count' => count($overlap),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function extractKeywords(string $text): array
    {
        $lower = mb_strtolower($text);
        $found = [];

        foreach (self::TECH_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) {
                $found[] = $kw;
            }
        }

        if (preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+){0,2}\b/u', $text, $m)) {
            foreach ($m[0] as $token) {
                if (mb_strlen($token) > 3 && mb_strlen($token) < 40) {
                    $found[] = $token;
                }
            }
        }

        return array_values(array_unique($found));
    }

    /**
     * @param  list<array<string, mixed>|string>  $aiMissing
     * @param  list<string>  $heuristicMissing
     * @return list<string>
     */
    private function mergeMissingSkills(array $aiMissing, array $heuristicMissing): array
    {
        $skills = [];

        foreach ($aiMissing as $item) {
            if (is_array($item)) {
                $skills[] = (string) ($item['keyword'] ?? $item['skill'] ?? '');
            } elseif (is_string($item)) {
                $skills[] = $item;
            }
        }

        foreach ($heuristicMissing as $kw) {
            $skills[] = ucfirst($kw);
        }

        $skills = array_values(array_unique(array_filter(array_map('trim', $skills))));

        return array_slice($skills, 0, 12);
    }

    /**
     * @param  list<array<string, mixed>>  $tailoring
     * @param  list<string>  $missingSkills
     * @param  list<string>  $keywordGaps
     * @return list<array<string, string>>
     */
    private function buildRecommendations(array $tailoring, array $missingSkills, array $keywordGaps): array
    {
        $recs = [];

        foreach ($tailoring as $item) {
            if (! is_array($item)) {
                continue;
            }
            $recs[] = [
                'type' => 'bullet_rewrite',
                'title' => 'Strengthen experience bullet',
                'detail' => (string) ($item['suggested_bullet'] ?? $item['feedback'] ?? ''),
                'original' => (string) ($item['original_bullet'] ?? ''),
            ];
        }

        foreach (array_slice($missingSkills, 0, 4) as $skill) {
            $recs[] = [
                'type' => 'missing_skill',
                'title' => "Add skill: {$skill}",
                'detail' => "Include \"{$skill}\" in your skills section or a quantified experience bullet.",
                'original' => '',
            ];
        }

        foreach (array_slice($keywordGaps, 0, 3) as $gap) {
            $label = is_string($gap) ? $gap : ($gap['keyword'] ?? '');
            if ($label === '') {
                continue;
            }
            $recs[] = [
                'type' => 'keyword_gap',
                'title' => "ATS keyword: {$label}",
                'detail' => "Weave \"{$label}\" naturally into a recent role bullet.",
                'original' => '',
            ];
        }

        return array_slice($recs, 0, 10);
    }
}
