<?php

namespace App\Support;

/**
 * Deterministic resume audit (Resume Worded–style heuristics).
 * Penalizes uncertain cases; does not reward technical keyword stuffing alone.
 */
class ResumeTextAuditor
{
    private const WEAK_VERBS = [
        'worked on', 'helped', 'assisted', 'handled', 'responsible for',
        'involved in', 'participated in', 'contributed to', 'supported', 'utilized',
    ];

    private const BUZZWORDS = [
        'team player', 'hardworking', 'hard-working', 'passionate', 'detail-oriented',
        'go-getter', 'synergy', 'leverage', 'dynamic', 'results-driven', 'self-motivated',
        'think outside the box', 'fast learner', 'excellent communication',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function audit(string $text): array
    {
        $text = trim($text);
        $lower = mb_strtolower($text);
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        $bullets = array_values(array_filter($lines, fn ($l) => (bool) preg_match('/^[•\-\*\x{2022}▪]|\d+[\.\)]\s+/u', trim($l))));

        $unquantifiedBullets = 0;
        $weakVerbBullets = 0;
        $longBullets = 0;
        $firstPersonBullets = 0;

        foreach ($bullets as $bullet) {
            $b = trim(preg_replace('/^[•\-\*\x{2022}▪\d\.\)]\s*/u', '', $bullet) ?? $bullet);
            if ($b === '') {
                continue;
            }
            if (! preg_match('/\d|%|\$|k\b|m\b|million|billion|users|clients|revenue/i', $b)) {
                $unquantifiedBullets++;
            }
            foreach (self::WEAK_VERBS as $verb) {
                if (preg_match('/\b'.preg_quote($verb, '/').'\b/i', $b)) {
                    $weakVerbBullets++;
                    break;
                }
            }
            if (mb_strlen($b) > 180) {
                $longBullets++;
            }
            if (preg_match('/\b(I|me|my|we|our)\b/i', $b)) {
                $firstPersonBullets++;
            }
        }

        $buzzwordCount = 0;
        foreach (self::BUZZWORDS as $bw) {
            if (str_contains($lower, $bw)) {
                $buzzwordCount++;
            }
        }

        $hasEmail = (bool) preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $text);
        $hasPhone = (bool) preg_match('/\+?\d[\d\s().-]{7,}\d/', $text);
        $hasLinkedIn = str_contains($lower, 'linkedin');
        $hasExperience = (bool) preg_match('/experience|employment|work history|خبر/i', $lower);
        $hasEducation = (bool) preg_match('/education|degree|university|تعليم/i', $lower);
        $hasSkills = (bool) preg_match('/skills|technologies|مهار/i', $lower);

        $dateRanges = [];
        if (preg_match_all('/\b((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{4}|\d{1,2}\/\d{4}|\d{4})\s*[-–—to]+\s*((?:present|current|now)|(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{4}|\d{4})/i', $text, $m)) {
            $dateRanges = $m[0];
        }

        $critical = [];
        if (! $hasEmail && ! $hasPhone) {
            $critical[] = 'Missing clear contact information (email or phone).';
        }
        if ($hasExperience && $unquantifiedBullets >= 3) {
            $critical[] = 'Multiple experience bullets lack measurable outcomes.';
        }
        if ($weakVerbBullets >= 3) {
            $critical[] = 'Frequent passive or weak action verbs in experience bullets.';
        }
        if (preg_match('/references available upon request/i', $lower)) {
            $critical[] = 'Outdated "References available upon request" section.';
        }

        $warnings = [];
        if ($buzzwordCount >= 2) {
            $warnings[] = 'Generic buzzwords detected.';
        }
        if ($longBullets >= 2) {
            $warnings[] = 'Bullets exceed recommended length.';
        }
        if ($firstPersonBullets >= 1) {
            $warnings[] = 'First-person pronouns in bullets reduce ATS impact.';
        }
        if (! $hasSkills) {
            $warnings[] = 'No dedicated skills section detected.';
        }

        $bulletTotal = max(1, count($bullets));
        $unquantifiedRatio = $unquantifiedBullets / $bulletTotal;

        return [
            'bullet_count' => count($bullets),
            'unquantified_bullets' => $unquantifiedBullets,
            'unquantified_ratio' => round($unquantifiedRatio, 2),
            'weak_verb_bullets' => $weakVerbBullets,
            'buzzword_count' => $buzzwordCount,
            'long_bullets' => $longBullets,
            'first_person_bullets' => $firstPersonBullets,
            'has_email' => $hasEmail,
            'has_phone' => $hasPhone,
            'has_linkedin' => $hasLinkedIn,
            'has_experience' => $hasExperience,
            'has_education' => $hasEducation,
            'has_skills' => $hasSkills,
            'date_range_count' => count($dateRanges),
            'critical_issues' => $critical,
            'critical_count' => count($critical),
            'warnings' => $warnings,
        ];
    }

    /**
     * Heuristic dimension scores (0–100) from text alone — conservative baseline.
     *
     * @return array<string, int>
     */
    public static function heuristicDimensionScores(array $audit): array
    {
        $impact = 100;
        $impact -= min(40, $audit['unquantified_bullets'] * 8);
        $impact -= min(15, $audit['weak_verb_bullets'] * 3);

        $brevity = 100;
        $brevity -= min(25, $audit['long_bullets'] * 8);
        $brevity -= min(15, $audit['first_person_bullets'] * 5);

        $style = 100;
        $style -= min(30, $audit['weak_verb_bullets'] * 6);
        $style -= min(20, $audit['buzzword_count'] * 8);

        $ats = 100;
        if (! $audit['has_email']) {
            $ats -= 12;
        }
        if (! $audit['has_skills']) {
            $ats -= 10;
        }
        if ($audit['buzzword_count'] >= 2) {
            $ats -= 8;
        }

        $keywords = 100 - min(35, $audit['unquantified_bullets'] * 5);

        $grammar = 92;
        $formatting = 88 - min(20, $audit['long_bullets'] * 5);

        $completeness = 100;
        if (! $audit['has_experience']) {
            $completeness -= 25;
        }
        if (! $audit['has_education']) {
            $completeness -= 10;
        }
        if (! $audit['has_skills']) {
            $completeness -= 15;
        }

        $dates = 90;
        if ($audit['date_range_count'] < 1 && $audit['has_experience']) {
            $dates -= 15;
        }

        $recruiter = (int) round(($impact + $style + $ats) / 3) - 5;

        return [
            'impact' => max(0, min(100, $impact)),
            'brevity' => max(0, min(100, $brevity)),
            'style' => max(0, min(100, $style)),
            'ats' => max(0, min(100, $ats)),
            'keywords' => max(0, min(100, $keywords)),
            'grammar' => max(0, min(100, $grammar)),
            'formatting' => max(0, min(100, $formatting)),
            'completeness' => max(0, min(100, $completeness)),
            'dates' => max(0, min(100, $dates)),
            'recruiter' => max(0, min(100, $recruiter)),
        ];
    }
}
