<?php

namespace App\Support;

/**
 * Resume Worded–aligned scoring: weighted dimensions, penalties, hard caps.
 */
class ResumeScoringEngine
{
    /** @var array<string, float> */
    public const WEIGHTS = [
        'impact' => 0.25,
        'brevity' => 0.15,
        'style' => 0.10,
        'ats' => 0.15,
        'keywords' => 0.15,
        'grammar' => 0.05,
        'formatting' => 0.05,
        'completeness' => 0.05,
        'dates' => 0.03,
        'recruiter' => 0.02,
    ];

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public function calibrate(array $result, string $resumeText): array
    {
        $audit = ResumeTextAuditor::audit($resumeText);
        $heuristic = ResumeTextAuditor::heuristicDimensionScores($audit);
        $dimensions = $this->resolveDimensions($result, $heuristic);

        $weightedScore = $this->weightedOverall($dimensions);
        $aiRaw = (int) ($result['overall_score'] ?? $weightedScore);
        $penalties = $this->computePenalties($result, $audit);

        // Resume Worded alignment: weighted + audit drive the score, not optimistic AI
        $base = min($aiRaw, $weightedScore);
        $final = max(0, min(100, (int) round($base - $penalties)));

        // Extra pull-down when AI is noticeably higher than evidence-based score
        if ($aiRaw > $weightedScore + 3) {
            $final = min($final, (int) round($weightedScore - $penalties));
        }

        $criticalIssues = array_values(array_unique(array_merge(
            $result['critical_issues'] ?? [],
            $audit['critical_issues'] ?? []
        )));
        $criticalCount = count($criticalIssues);

        $final = $this->applyScoreCaps($final, $criticalCount, $audit);

        $result['overall_score'] = $final;
        $result['ats_score'] = $dimensions['ats'];
        $result['recruiter_score'] = $dimensions['recruiter'];
        $result['impact_score'] = $dimensions['impact'];
        $result['brevity_score'] = $dimensions['brevity'];
        $result['style_score'] = $dimensions['style'];
        $result['keyword_match_score'] = $dimensions['keywords'];
        $result['ats_compatibility_score'] = $dimensions['ats'];
        $result['critical_issues'] = $criticalIssues;

        $result['scoring_meta'] = [
            'ai_raw_score' => $aiRaw,
            'weighted_score' => $weightedScore,
            'heuristic_dimensions' => $heuristic,
            'final_dimensions' => $dimensions,
            'penalties_applied' => $penalties,
            'audit' => $audit,
            'calibration' => 'resume_worded_strict_v1',
            'formula' => 'min(AI, weighted) - penalties, then caps',
        ];

        $result['score_headline'] = "Your resume scored {$final}/100. ".$this->headlineTone($final);
        if (empty($result['score_explanation'])) {
            $result['score_explanation'] = $this->buildExplanation($final, $audit, $criticalCount);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, int>  $heuristic
     * @return array<string, int>
     */
    private function resolveDimensions(array $result, array $heuristic): array
    {
        $sections = $result['section_scores'] ?? [];
        $fromSection = function (string $key) use ($sections): int {
            return (int) ($sections[$key]['score'] ?? 0);
        };

        $map = [
            'impact' => [
                (int) ($result['impact_score'] ?? 0),
                $fromSection('impact'),
                $fromSection('experience'),
                $this->checkScore($result, 'growth_signals'),
                $heuristic['impact'],
            ],
            'brevity' => [
                (int) ($result['brevity_score'] ?? 0),
                $this->checkScore($result, 'readability'),
                $fromSection('summary'),
                $heuristic['brevity'],
            ],
            'style' => [
                (int) ($result['style_score'] ?? 0),
                $fromSection('action_verbs'),
                $fromSection('repetition'),
                $heuristic['style'],
            ],
            'ats' => [
                (int) ($result['ats_score'] ?? $result['ats_compatibility_score'] ?? 0),
                $fromSection('ats'),
                $heuristic['ats'],
            ],
            'keywords' => [
                (int) ($result['keyword_match_score'] ?? 0),
                $fromSection('keywords'),
                $heuristic['keywords'],
            ],
            'grammar' => [
                $fromSection('grammar'),
                $heuristic['grammar'],
            ],
            'formatting' => [
                $fromSection('formatting'),
                $heuristic['formatting'],
            ],
            'completeness' => [
                $this->completenessFromSections($sections),
                $heuristic['completeness'],
            ],
            'dates' => [
                $this->checkScore($result, 'dates'),
                $fromSection('dates'),
                $fromSection('date_overlaps'),
                $fromSection('employment_gaps'),
                $heuristic['dates'],
            ],
            'recruiter' => [
                (int) ($result['recruiter_score'] ?? 0),
                $heuristic['recruiter'],
            ],
        ];

        $out = [];
        foreach ($map as $key => $candidates) {
            $valid = array_values(array_filter($candidates, fn ($v) => $v > 0));
            if ($valid === []) {
                $out[$key] = $heuristic[$key];
                continue;
            }
            // Conservative: use minimum of AI signals, not average
            $out[$key] = max(0, min(100, (int) min($valid)));
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $sections
     */
    private function completenessFromSections(array $sections): int
    {
        $keys = ['contact', 'summary', 'experience', 'skills', 'education'];
        $found = 0;
        foreach ($keys as $k) {
            if (isset($sections[$k]) && ($sections[$k]['score'] ?? 0) > 0) {
                $found++;
            }
        }

        return (int) round(($found / count($keys)) * 100);
    }

    /**
     * @param  array<string, int>  $dimensions
     */
    private function weightedOverall(array $dimensions): int
    {
        $sum = 0.0;
        foreach (self::WEIGHTS as $key => $weight) {
            $sum += $weight * ($dimensions[$key] ?? 0);
        }

        return (int) round($sum);
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $audit
     */
    private function computePenalties(array $result, array $audit): int
    {
        $penalty = 0;
        $penalty += min(18, $audit['unquantified_bullets'] * 3);
        $penalty += min(15, $audit['weak_verb_bullets'] * 3);
        $penalty += min(12, $audit['buzzword_count'] * 4);
        $penalty += min(8, $audit['long_bullets'] * 2);
        $penalty += min(6, $audit['first_person_bullets'] * 3);

        if (! $audit['has_email']) {
            $penalty += 4;
        }
        if (! $audit['has_skills']) {
            $penalty += 3;
        }

        $grammarCount = count($result['grammar_errors'] ?? []);
        $penalty += min(10, $grammarCount * 2);

        $formattingCount = count($result['formatting_issues'] ?? []);
        $penalty += min(8, $formattingCount * 2);

        $keywordGaps = count($result['keyword_gaps'] ?? []);
        $penalty += min(10, max(0, $keywordGaps - 2) * 2);

        return min(35, $penalty);
    }

    /**
     * @param  array<string, mixed>  $audit
     */
    private function applyScoreCaps(int $score, int $criticalCount, array $audit): int
    {
        if ($criticalCount >= 2) {
            $score = min($score, 70);
        } elseif ($criticalCount >= 1) {
            $score = min($score, 75);
        }

        if (($audit['unquantified_ratio'] ?? 0) >= 0.65) {
            $score = min($score, 72);
        }
        if (($audit['weak_verb_bullets'] ?? 0) >= 4) {
            $score = min($score, 74);
        }
        if (($audit['buzzword_count'] ?? 0) >= 3) {
            $score = min($score, 73);
        }

        return $score;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function checkScore(array $result, string $checkId): int
    {
        foreach ($result['checks'] ?? [] as $check) {
            if (is_array($check) && ($check['id'] ?? '') === $checkId) {
                return (int) ($check['score'] ?? 0);
            }
        }

        return 0;
    }

    private function headlineTone(int $score): string
    {
        if ($score >= 80) {
            return 'Strong profile with a few high-impact fixes remaining.';
        }
        if ($score >= 70) {
            return 'Solid foundation — measurable impact and ATS polish will move the needle.';
        }
        if ($score >= 60) {
            return 'Several fixable issues are holding back recruiter and ATS performance.';
        }

        return 'Major revisions recommended before applying to competitive roles.';
    }

    /**
     * @param  array<string, mixed>  $audit
     */
    private function buildExplanation(int $score, array $audit, int $criticalCount): string
    {
        $parts = [];
        if ($audit['unquantified_bullets'] > 0) {
            $parts[] = "{$audit['unquantified_bullets']} experience bullet(s) lack numbers or outcomes.";
        }
        if ($audit['weak_verb_bullets'] > 0) {
            $parts[] = "{$audit['weak_verb_bullets']} bullet(s) use weak or passive phrasing.";
        }
        if ($criticalCount > 0) {
            $parts[] = "{$criticalCount} critical issue(s) triggered score caps.";
        }
        if ($parts === []) {
            return 'Review section scores and line-level feedback to prioritize improvements.';
        }

        return implode(' ', $parts)." Calibrated overall: {$score}/100.";
    }
}
