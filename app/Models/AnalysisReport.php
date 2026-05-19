<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'resume_id',
    'user_id',
    'overall_score',
    'ats_score',
    'recruiter_score',
    'keyword_match_score',
    'executive_summary',
    'feedback_data',
    'full_analysis',
    'impact_score',
    'brevity_score',
    'style_score',
])]
class AnalysisReport extends Model
{
    protected function casts(): array
    {
        return [
            'feedback_data' => 'array',
            'full_analysis' => 'array',
            'impact_score' => 'array',
            'brevity_score' => 'array',
            'style_score' => 'array',
        ];
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
