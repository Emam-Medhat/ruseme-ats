<?php

namespace App\Support;

use App\Models\AnalysisReport;

class ResumeAnalysisNormalizer
{
    /**
     * Normalize legacy + new AI payloads into one shape for the Report UI.
     *
     * @param  array<string, mixed>|string|null  $data
     * @return array<string, mixed>|null
     */
    public static function normalize(array|string|null $data, ?AnalysisReport $report = null): ?array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : null;
        }

        if (! is_array($data) || $data === []) {
            return null;
        }

        $a = $data;

        if (isset($a['final_score'])) {
            $a['overall_score'] = (int) $a['final_score'];
            
            $newSec = $a['sections'] ?? [];
            
            $a['ats_score'] = (int) ($newSec['ats_compatibility']['score'] ?? 0);
            $a['ats_compatibility_score'] = $a['ats_score'];
            $a['keyword_match_score'] = (int) ($newSec['keywords']['score'] ?? 0);
            $a['impact_score'] = (int) ($newSec['impact_metrics']['score'] ?? 0);
            $a['brevity_score'] = (int) ($newSec['brevity']['score'] ?? 0);
            $a['style_score'] = (int) ($newSec['formatting']['score'] ?? 0);
            $a['recruiter_score'] = (int) ($newSec['sections']['score'] ?? 0);
            
            $a['critical_issues'] = (array) ($a['critical_issues'] ?? []);
            
            $a['top_recommendations'] = [];
            foreach ((array) ($a['top_suggestions'] ?? []) as $idx => $suggestion) {
                $a['top_recommendations'][] = [
                    'id' => $idx + 1,
                    'original_line' => '',
                    'improved_line' => '',
                    'issue' => $suggestion,
                ];
            }
            
            $a['grammar_errors'] = [];
            foreach ((array) ($newSec['grammar']['issues'] ?? []) as $issue) {
                $a['grammar_errors'][] = [
                    'line' => '',
                    'issue' => $issue,
                    'fix' => ''
                ];
            }
            
            $a['keyword_gaps'] = (array) ($newSec['keywords']['missing_keywords'] ?? []);
            $a['recommended_keywords'] = (array) ($newSec['keywords']['found_keywords'] ?? []);
            
            $a['section_scores'] = [
                'ats' => [
                    'score' => $a['ats_score'],
                    'feedback' => implode('. ', (array) ($newSec['ats_compatibility']['suggestions'] ?? []))
                ],
                'keywords' => [
                    'score' => $a['keyword_match_score'],
                    'feedback' => implode('. ', (array) ($newSec['keywords']['suggestions'] ?? []))
                ],
                'experience' => [
                    'score' => $a['impact_score'],
                    'feedback' => implode('. ', (array) ($newSec['impact_metrics']['suggestions'] ?? []))
                ],
                'formatting' => [
                    'score' => $a['style_score'],
                    'feedback' => implode('. ', (array) ($newSec['formatting']['suggestions'] ?? []))
                ],
                'summary' => [
                    'score' => $a['brevity_score'],
                    'feedback' => implode('. ', (array) ($newSec['brevity']['suggestions'] ?? []))
                ],
                'grammar' => [
                    'score' => (int) ($newSec['grammar']['score'] ?? 0),
                    'feedback' => implode('. ', (array) ($newSec['grammar']['issues'] ?? []))
                ],
                'contact' => [
                    'score' => $a['recruiter_score'],
                    'feedback' => ''
                ],
                'education' => [
                    'score' => $a['recruiter_score'],
                    'feedback' => ''
                ],
                'skills' => [
                    'score' => $a['recruiter_score'],
                    'feedback' => ''
                ]
            ];
            
            $a['formatting_issues'] = (array) ($newSec['formatting']['issues'] ?? []);
            
            $a['checks'] = [
                [
                    'id' => 'readability',
                    'name' => 'Readability',
                    'score' => $a['brevity_score'],
                    'status' => $a['brevity_score'] >= 75 ? 'passed' : 'issue',
                    'issue_count' => count((array) ($newSec['brevity']['issues'] ?? [])),
                    'points_impact' => $a['brevity_score'] >= 75 ? 10 : -10,
                    'title' => $a['brevity_score'] >= 75 ? 'Readability looks good' : 'Readability needs work',
                    'description' => implode('. ', (array) ($newSec['brevity']['suggestions'] ?? [])),
                    'issues' => array_map(fn($issue) => [
                        'original_line' => $issue,
                        'improved_line' => '',
                        'reason' => 'Readability issue'
                    ], (array) ($newSec['brevity']['issues'] ?? []))
                ],
                [
                    'id' => 'growth_signals',
                    'name' => 'Growth signals',
                    'score' => $a['impact_score'],
                    'status' => $a['impact_score'] >= 75 ? 'passed' : 'issue',
                    'issue_count' => count((array) ($newSec['impact_metrics']['weak_bullets'] ?? [])),
                    'points_impact' => $a['impact_score'] >= 75 ? 10 : -15,
                    'title' => $a['impact_score'] >= 75 ? 'Strong impact signals' : 'Add measurable achievements',
                    'description' => implode('. ', (array) ($newSec['impact_metrics']['suggestions'] ?? [])),
                    'issues' => array_map(fn($bullet) => [
                        'original_line' => $bullet,
                        'improved_line' => '',
                        'reason' => 'Bullet point lacks quantified metrics'
                    ], (array) ($newSec['impact_metrics']['weak_bullets'] ?? []))
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
                    'score' => $a['style_score'],
                    'status' => $a['style_score'] >= 75 ? 'passed' : 'issue',
                    'issue_count' => count((array) ($newSec['formatting']['issues'] ?? [])),
                    'points_impact' => $a['style_score'] >= 75 ? 10 : -5,
                    'title' => 'Formatting & consistency',
                    'description' => implode('. ', (array) ($newSec['formatting']['suggestions'] ?? [])),
                    'issues' => array_map(fn($issue) => [
                        'original_line' => $issue,
                        'improved_line' => '',
                        'reason' => 'Formatting issue'
                    ], (array) ($newSec['formatting']['issues'] ?? []))
                ],
            ];
            
            $a['executive_summary'] = implode('. ', (array) ($a['top_suggestions'] ?? []));
        }

        if ($report) {
            $a['overall_score'] ??= (int) $report->overall_score;
            $a['ats_score'] ??= (int) ($report->ats_score ?? 0);
            $a['ats_compatibility_score'] ??= (int) ($report->ats_score ?? 0);
            $a['recruiter_score'] ??= (int) ($report->recruiter_score ?? 0);
            $a['keyword_match_score'] ??= (int) ($report->keyword_match_score ?? 0);
            $a['executive_summary'] ??= (string) ($report->executive_summary ?? '');

            if (! isset($a['impact_score']) && is_array($report->impact_score)) {
                $a['impact_score'] = (int) ($report->impact_score['score'] ?? 0);
            }
            if (! isset($a['brevity_score']) && is_array($report->brevity_score)) {
                $a['brevity_score'] = (int) ($report->brevity_score['score'] ?? 0);
            }
            if (! isset($a['style_score']) && is_array($report->style_score)) {
                $a['style_score'] = (int) ($report->style_score['score'] ?? 0);
            }
        }

        $a['ats_score'] ??= $a['ats_compatibility_score'] ?? 0;
        $a['ats_compatibility_score'] = (int) ($a['ats_compatibility_score'] ?? $a['ats_score'] ?? 0);
        $a['recruiter_score'] ??= (int) ($a['recruiter_score'] ?? 0);
        $a['keyword_match_score'] ??= (int) ($a['keyword_match_score'] ?? 0);
        $a['impact_score'] ??= (int) ($a['impact_score'] ?? 0);
        $a['brevity_score'] ??= (int) ($a['brevity_score'] ?? 0);
        $a['style_score'] ??= (int) ($a['style_score'] ?? 0);
        $a['executive_summary'] ??= '';
        $a['detected_profession'] ??= '';
        $a['section_scores'] ??= [];
        $a['grammar_errors'] ??= [];
        $a['keyword_gaps'] ??= [];
        $a['formatting_issues'] ??= [];
        $a['recommended_keywords'] ??= [];
        $a['action_verbs_suggestions'] ??= [];
        $a['rewritten_bullets'] ??= [];
        $a['detailed_line_by_line_feedback'] ??= [];
        $a['weaknesses'] ??= [];
        $a['ai_ensemble'] ??= false;
        $a['ai_providers_list'] ??= [];

        if (isset($a['category_scores']) && is_array($a['category_scores'])) {
            $a['impact_score'] ??= (int) ($a['category_scores']['impact']['score'] ?? 0);
            $a['brevity_score'] ??= (int) ($a['category_scores']['brevity']['score'] ?? 0);
            $a['style_score'] ??= (int) ($a['category_scores']['style']['score'] ?? 0);
        }

        if (empty($a['sections']) && ! empty($a['section_scores']) && is_array($a['section_scores'])) {
            $a['sections'] = self::mapCvGeniusSectionScores($a['section_scores']);
        }

        if (empty($a['sections']) && ! empty($a['section_analysis']) && is_array($a['section_analysis'])) {
            $a['sections'] = self::mapSectionAnalysis($a['section_analysis']);
        }

        if (empty($a['top_recommendations']) && ! empty($a['rewritten_bullets'])) {
            $a['top_recommendations'] = array_values(array_map(function (array $item, int $idx) {
                return [
                    'id' => $idx + 1,
                    'original_line' => $item['original'] ?? $item['original_line'] ?? '',
                    'improved_line' => $item['improved'] ?? $item['improved_line'] ?? '',
                    'issue' => $item['reason'] ?? 'Suggested rewrite',
                ];
            }, $a['rewritten_bullets'], array_keys($a['rewritten_bullets'])));
        }

        if (! empty($a['top_recommendations']) && is_array($a['top_recommendations'])) {
            $a['top_recommendations'] = array_values(array_map(function (array $rec, int $idx) {
                $rec['id'] = $rec['id'] ?? ($idx + 1);
                $rec['original_line'] ??= $rec['original_text'] ?? null;
                $rec['improved_line'] ??= $rec['draft_improvement'] ?? null;
                $rec['issue'] ??= $rec['title'] ?? $rec['feedback'] ?? '';

                return $rec;
            }, $a['top_recommendations'], array_keys($a['top_recommendations'])));
        }

        if (empty($a['critical_issues']) && ! empty($a['detailed_issues_groups'])) {
            $a['critical_issues'] = self::extractCriticalFromGroups($a['detailed_issues_groups']);
        }

        $a['strengths'] ??= [];
        $a['critical_issues'] ??= [];
        $a['roadmap'] ??= [];
        $a['missing_keywords'] ??= [];
        $a['top_recommendations'] ??= [];
        $a['sections'] ??= [];

        return $a;
    }

    /**
     * @param  array<string, array<string, mixed>>  $sectionScores
     * @return array<string, array<string, mixed>>
     */
    private static function mapCvGeniusSectionScores(array $sectionScores): array
    {
        $sections = [];

        foreach ($sectionScores as $key => $data) {
            if (! is_array($data)) {
                continue;
            }
            $sections[$key] = [
                'score' => (int) ($data['score'] ?? 0),
                'found' => true,
                'issues' => [],
                'suggestions' => array_filter([(string) ($data['feedback'] ?? '')]),
            ];
        }

        return $sections;
    }

    /**
     * @param  list<array<string, mixed>>  $sectionAnalysis
     * @return array<string, array<string, mixed>>
     */
    private static function mapSectionAnalysis(array $sectionAnalysis): array
    {
        $sections = [];

        foreach ($sectionAnalysis as $sec) {
            $name = strtolower((string) ($sec['name'] ?? 'other'));
            $key = match ($name) {
                'skills', 'skill' => 'skills',
                'experience', 'work experience' => 'experience',
                'education' => 'education',
                'summary', 'profile', 'objective' => 'summary',
                'projects', 'project' => 'projects',
                'languages', 'language' => 'languages',
                'certifications', 'certification' => 'certifications',
                default => preg_replace('/\s+/', '_', $name) ?: 'other',
            };

            $sections[$key] = [
                'score' => (int) ($sec['score'] ?? 0),
                'found' => true,
                'issues' => $sec['issues'] ?? [],
                'suggestions' => ! empty($sec['feedback_summary'])
                    ? array_filter([(string) $sec['feedback_summary']])
                    : [],
            ];
        }

        return $sections;
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return list<string>
     */
    private static function extractCriticalFromGroups(array $groups): array
    {
        $issues = [];

        foreach ($groups as $group) {
            foreach ($group['issues'] ?? [] as $issue) {
                if (is_string($issue) && $issue !== '') {
                    $issues[] = $issue;
                }
            }
        }

        return array_slice($issues, 0, 8);
    }
}
