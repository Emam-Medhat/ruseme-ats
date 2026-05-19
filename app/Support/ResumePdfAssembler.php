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
     * @return list<array{degree: string, school: string, dates: string}>
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
            $entries[] = [
                'degree' => $line,
                'school' => '',
                'dates' => '',
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
