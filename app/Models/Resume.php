<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'title',
    'filename',
    'file_path',
    'status',
    'original_file_path',
    'parsed_text',
])]
class Resume extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function analysisReports()
    {
        return $this->hasMany(AnalysisReport::class);
    }

    public function reports()
    {
        return $this->hasMany(AnalysisReport::class);
    }

    public function latestReport(): HasOne
    {
        return $this->hasOne(AnalysisReport::class)->latestOfMany();
    }

    public function jobMatches()
    {
        return $this->hasMany(JobMatch::class);
    }
}
