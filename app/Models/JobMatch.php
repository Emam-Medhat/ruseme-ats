<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'resume_id',
    'job_title',
    'job_description',
    'match_score',
    'missing_keywords',
    'weak_sentences',
    'improved_sentences',
    'ai_raw_response',
])]
class JobMatch extends Model
{
    protected function casts(): array
    {
        return [
            'missing_keywords' => 'array',
            'weak_sentences' => 'array',
            'improved_sentences' => 'array',
            'ai_raw_response' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }

    public function toMatchResult(): array
    {
        if (! empty($this->ai_raw_response)) {
            return $this->ai_raw_response;
        }

        $tailoringSuggestions = [];
        $weak = $this->weak_sentences ?? [];
        $improved = $this->improved_sentences ?? [];

        foreach ($weak as $index => $sentence) {
            $improvedSentence = $improved[$index] ?? [];
            $tailoringSuggestions[] = [
                'section' => is_array($sentence) ? ($sentence['section'] ?? 'Experience') : 'Experience',
                'original_bullet' => is_array($sentence) ? ($sentence['original_bullet'] ?? '') : $sentence,
                'feedback' => is_array($sentence) ? ($sentence['feedback'] ?? '') : '',
                'suggested_bullet' => is_array($improvedSentence)
                    ? ($improvedSentence['suggested_bullet'] ?? '')
                    : (string) $improvedSentence,
            ];
        }

        return [
            'match_score' => $this->match_score,
            'summary' => 'Cached compatibility result for '.$this->job_title,
            'missing_keywords' => $this->missing_keywords ?? [],
            'tailoring_suggestions' => $tailoringSuggestions,
        ];
    }
}
