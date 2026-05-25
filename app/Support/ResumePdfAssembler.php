<?php

namespace App\Support;

/**
 * Builds a complete, ATS-safe resume structure for PDF export.
 */
class ResumePdfAssembler
{
    /**
     * @param  array<string, mixed>  $sections  Live/edited resume_sections from UI
     * @return array<string, mixed>
     */
    public function assemble(array $sections, string $parsedText = ''): array
    {
        $parsedText = trim($parsedText);
        $parsed = $parsedText !== '' ? (new ResumePreviewBuilder())->build($parsedText, ['resume_sections' => $sections]) : $sections;

        $merged = $this->mergeSections($parsed, $sections);

        $experience = $this->normalizeExperience($merged['experience'] ?? []);
        if ($experience === [] && $parsedText !== '') {
            $parsedOnly = (new ResumePreviewBuilder())->build($parsedText, null);
            $merged = $this->mergeSections($parsedOnly, $sections);
            $experience = $this->normalizeExperience($merged['experience'] ?? []);
        }

        return [
            'name' => $this->cleanLine($merged['name'] ?? 'Candidate'),
            'title' => $this->cleanLine($merged['title'] ?? ''),
            'contact' => $this->cleanLine($merged['contact'] ?? ''),
            'links' => array_values(array_filter($merged['links'] ?? [], fn ($l) => is_string($l) && $l !== '')),
            'summary_text' => $this->cleanParagraph($merged['summary_text'] ?? ''),
            'experience' => $experience,
            'skills' => $this->normalizeSkills($merged['skills_text'] ?? ''),
            'skills_text' => $this->cleanParagraph($merged['skills_text'] ?? ''),
            'education' => $this->normalizeEducation($merged['education'] ?? ''),
            'education_text' => $this->cleanParagraph($merged['education'] ?? ''),
            'projects' => $this->cleanParagraph($merged['projects_text'] ?? ''),
            'languages' => $this->cleanParagraph($merged['languages_text'] ?? ''),
            'certifications' => $this->cleanParagraph($merged['certifications_text'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overlay
     * @return array<string, mixed>
     */
    private function mergeSections(array $base, array $overlay): array
    {
        $out = $base;

        foreach (['name', 'title', 'contact', 'summary_text', 'skills_text', 'education', 'projects_text', 'languages_text', 'certifications_text'] as $key) {
            if (! empty($overlay[$key])) {
                $out[$key] = $overlay[$key];
            }
        }

        if (! empty($overlay['links'])) {
            $out['links'] = $overlay['links'];
        }

        $out['experience'] = $this->mergeExperienceJobs(
            $base['experience'] ?? [],
            $overlay['experience'] ?? []
        );

        return $out;
    }

    /**
     * Prefer live UI bullets (magic rewrites) while keeping parsed job titles/dates.
     *
     * @param  list<array<string, mixed>>  $baseJobs
     * @param  list<array<string, mixed>>  $overlayJobs
     * @return list<array<string, mixed>>
     */
    private function mergeExperienceJobs(array $baseJobs, array $overlayJobs): array
    {
        if ($overlayJobs === []) {
            return $baseJobs;
        }
        if ($baseJobs === []) {
            return $overlayJobs;
        }

        $merged = [];
        $count = max(count($baseJobs), count($overlayJobs));

        for ($i = 0; $i < $count; $i++) {
            $base = $baseJobs[$i] ?? [];
            $over = $overlayJobs[$i] ?? [];

            $merged[] = [
                'job_title' => $this->cleanLine($over['job_title'] ?? $base['job_title'] ?? ''),
                'company' => $this->cleanLine($over['company'] ?? $base['company'] ?? ''),
                'dates' => $this->cleanLine($over['dates'] ?? $base['dates'] ?? ''),
                'bullets' => $this->mergeJobBullets($base['bullets'] ?? [], $over['bullets'] ?? []),
            ];
        }

        return $merged;
    }

    /**
     * @param  list<mixed>  $baseBullets
     * @param  list<mixed>  $overlayBullets
     * @return list<mixed>
     */
    private function mergeJobBullets(array $baseBullets, array $overlayBullets): array
    {
        if ($overlayBullets === []) {
            return $baseBullets;
        }
        if ($baseBullets === []) {
            return $overlayBullets;
        }

        $overlayTexts = array_map(fn ($b) => $this->bulletText($b), $overlayBullets);
        $baseTexts = array_map(fn ($b) => $this->bulletText($b), $baseBullets);

        if ($overlayTexts === $baseTexts) {
            return $overlayBullets;
        }

        $used = [];
        $result = [];

        foreach ($baseBullets as $idx => $baseBullet) {
            $baseText = $this->bulletText($baseBullet);
            $replacement = $overlayBullets[$idx] ?? null;

            if ($replacement !== null) {
                $overText = $this->bulletText($replacement);
                if ($overText !== '' && ($overText !== $baseText || $this->isBulletObject($replacement))) {
                    $result[] = $replacement;
                    $used[$idx] = true;

                    continue;
                }
            }

            foreach ($overlayBullets as $oIdx => $overBullet) {
                if (isset($used[$oIdx])) {
                    continue;
                }
                $overText = $this->bulletText($overBullet);
                if ($overText === '') {
                    continue;
                }
                if (
                    $overText === $baseText
                    || str_contains($baseText, $overText)
                    || str_contains($overText, $baseText)
                ) {
                    $result[] = $overBullet;
                    $used[$oIdx] = true;

                    continue 2;
                }
            }

            $result[] = $baseBullet;
        }

        foreach ($overlayBullets as $oIdx => $overBullet) {
            if (! isset($used[$oIdx])) {
                $result[] = $overBullet;
            }
        }

        return $result;
    }

    private function bulletText(mixed $bullet): string
    {
        if (is_array($bullet)) {
            return $this->cleanLine((string) ($bullet['text'] ?? ''));
        }

        return $this->cleanLine((string) $bullet);
    }

    private function isBulletObject(mixed $bullet): bool
    {
        return is_array($bullet) && array_key_exists('text', $bullet);
    }

    /**
     * @param  list<array<string, mixed>>|mixed  $experience
     * @return list<array{job_title: string, company: string, dates: string, bullets: list<string>}>
     */
    private function normalizeExperience($experience): array
    {
        if (! is_array($experience)) {
            return [];
        }

        $jobs = [];

        foreach ($experience as $job) {
            if (! is_array($job)) {
                continue;
            }

            $bullets = [];
            foreach ($job['bullets'] ?? [] as $bullet) {
                $text = is_array($bullet) ? ($bullet['text'] ?? '') : (string) $bullet;
                $text = $this->cleanLine($text);
                if ($text !== '') {
                    $bullets[] = $text;
                }
            }

            $jobTitle = $this->cleanLine($job['job_title'] ?? '');
            $company = $this->cleanLine($job['company'] ?? '');
            $dates = $this->cleanLine($job['dates'] ?? '');

            if ($jobTitle === '' && $company === '' && $bullets === []) {
                continue;
            }

            $jobs[] = [
                'job_title' => $jobTitle ?: 'Role',
                'company' => $company,
                'dates' => $dates,
                'bullets' => $bullets,
            ];
        }

        return $jobs;
    }

    /**
     * @return list<string>
     */
    private function normalizeSkills(string $skillsText): array
    {
        $skillsText = $this->cleanParagraph($skillsText);
        if ($skillsText === '') {
            return [];
        }

        $parts = preg_split('/[,;|·•\n]+/', $skillsText) ?: [];

        return array_values(array_unique(array_filter(array_map(
            fn ($s) => $this->cleanLine($s),
            $parts
        ))));
    }

    /**
     * @return list<array{degree: string, school: string, dates: string, honors: string}>
     */
    private function normalizeEducation(string $education): array
    {
        $education = $this->cleanParagraph($education);
        if ($education === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $education) ?: [$education];
        $entries = [];

        foreach ($lines as $line) {
            $line = $this->cleanLine($line);
            if ($line === '') {
                continue;
            }

            // Extract honors first if present
            $honors = '';
            // Heuristic for honors (e.g. GPA 3.9, Honors, Cum Laude, Distinction, Grade A, etc. and Arabic terms)
            $honorsRegex = '/\b(summa\s+)?cum\s+laude\b|\bmagna\s+cum\s+laude\b|\bdistinction\b|\bmerit\b|\bfirst\s+class\b|\bhonou?rs\b|\bgpa\s*[\d\.]+\/?[\d\.]*|\bgrade\s*[A-F][+-]?|\bامتياز\b|\bتقدير\b|\bبدرجة\s+شرف\b/i';
            if (preg_match($honorsRegex, $line, $matches)) {
                // If it's part of parentheses, let's extract the whole parentheses content if it has honors keywords
                if (preg_match('/\(([^)]*(?:summa|cum\s+laude|magna|distinction|merit|honou?rs|gpa|grade|امتياز|تقدير)[^)]*)\)/i', $line, $parenMatch)) {
                    $honors = $parenMatch[1];
                    $line = str_replace($parenMatch[0], '', $line);
                } else {
                    $honors = $matches[0];
                    $line = str_replace($matches[0], '', $line);
                }
            }

            // Extract dates first if present to make parsing school and degree easier
            $dates = '';
            $dateRegex = '/\b((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*[\s\.\,]*\d{4}|\d{4})\s*[\-–—\s\btoحتى]+\s*((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*[\s\.\,]*\d{4}|\d{4}|present|current|الآن|حتى الآن|حالي)\b|\b\d{4}\b/i';
            if (preg_match($dateRegex, $line, $matches)) {
                // Check if enclosed in parentheses
                if (preg_match('/\(([^)]*(?:19|20)\d{2}[^)]*)\)/i', $line, $parenMatch)) {
                    $dates = $parenMatch[1];
                    $line = str_replace($parenMatch[0], '', $line);
                } else {
                    $dates = $matches[0];
                    $line = str_replace($matches[0], '', $line);
                }
            }

            // Clean up delimiters after extraction
            $line = preg_replace('/\s*[\-–—,|\(\)\|]+\s*$/', '', $line);
            $line = preg_replace('/^\s*[\-–—,|\(\)\|]+\s*/', '', $line);

            // Now parse school and degree from the remaining part
            $parts = preg_split('/\s*,\s*|\s*-\s*|\s*\|\s*|\s*–\s*|\s*—\s*/', $line);
            $degree = $line;
            $school = '';

            if (count($parts) >= 2) {
                // Detect school/university
                $schoolIdx = -1;
                foreach ($parts as $idx => $part) {
                    if (preg_match('/university|college|institute|school|academy|جامعة|معهد|كلية/i', $part)) {
                        $schoolIdx = $idx;
                        break;
                    }
                }

                if ($schoolIdx !== -1) {
                    $school = $parts[$schoolIdx];
                    unset($parts[$schoolIdx]);
                    $degree = implode(', ', array_map('trim', $parts));
                } else {
                    // If no school keyword, assume first part is school if it contains degree-like words
                    if (preg_match('/\b(bachelor|master|doctor|phd|diploma|bsc|msc|ba|ma|bba|mba|degree|بكالوريوس|ماجستير|دكتوراه|دبلوم)\b/i', $parts[1])) {
                        $school = $parts[0];
                        $degree = $parts[1];
                    } else {
                        $degree = $parts[0];
                        $school = $parts[1];
                    }
                }
            }

            $entries[] = [
                'degree' => $this->cleanLine($degree),
                'school' => $this->cleanLine($school),
                'dates' => $this->cleanLine($dates),
                'honors' => $this->cleanLine($honors),
            ];
        }

        return $entries;
    }

    private function cleanLine(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function cleanParagraph(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
