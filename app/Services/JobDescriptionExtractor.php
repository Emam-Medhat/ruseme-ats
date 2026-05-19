<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class JobDescriptionExtractor
{
    /**
     * Resolve job title + description from URL and/or pasted text.
     *
     * @return array{title: string, description: string, source: string, warning: string|null}
     */
    public function resolve(?string $jobUrl, ?string $jobDescription, ?string $jobTitle = null): array
    {
        $description = trim((string) $jobDescription);
        $title = trim((string) $jobTitle);
        $url = trim((string) $jobUrl);

        if ($description !== '' && mb_strlen($description) >= 50) {
            return [
                'title' => $title ?: $this->inferTitleFromDescription($description),
                'description' => $description,
                'source' => $url !== '' ? 'url_and_text' : 'text',
                'warning' => null,
            ];
        }

        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            $scraped = $this->attemptUrlParse($url);
            if ($scraped['description'] !== '') {
                return [
                    'title' => $title ?: $scraped['title'],
                    'description' => $scraped['description'],
                    'source' => 'url_scrape',
                    'warning' => $scraped['warning'],
                ];
            }

            return [
                'title' => $title ?: $scraped['title'],
                'description' => '',
                'source' => 'url_only',
                'warning' => 'Could not extract full job text from the URL. Paste the job description below.',
            ];
        }

        throw new \InvalidArgumentException('Provide a job description (min 50 characters) or a valid job URL.');
    }

    /**
     * @return array{title: string, description: string, warning: string|null}
     */
    private function attemptUrlParse(string $url): array
    {
        $title = $this->inferTitleFromUrl($url);
        $warning = null;
        $description = '';

        if (str_contains(mb_strtolower($url), 'linkedin.com')) {
            $warning = 'LinkedIn blocks automated scraping. Paste the job description for accurate matching.';
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'CVGeniusBot/1.0'])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                $description = $this->stripHtmlToText($html);
                if (mb_strlen($description) > 20000) {
                    $description = mb_substr($description, 0, 20000);
                }
                if (mb_strlen($description) < 50) {
                    $description = '';
                }
            }
        } catch (\Throwable) {
            $warning ??= 'Unable to fetch job page. Paste the description manually.';
        }

        return [
            'title' => $title,
            'description' => $description,
            'warning' => $warning,
        ];
    }

    private function inferTitleFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $slug = trim(str_replace(['-', '_'], ' ', basename($path)), '/');

        return $slug !== '' && $slug !== 'jobs' ? Str::title($slug) : 'Target Role';
    }

    private function inferTitleFromDescription(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $first = trim($lines[0] ?? '');

        return mb_strlen($first) <= 80 ? $first : 'Target Role';
    }

    private function stripHtmlToText(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
