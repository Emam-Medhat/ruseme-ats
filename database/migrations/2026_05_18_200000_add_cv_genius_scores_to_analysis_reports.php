<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('analysis_reports', 'recruiter_score')) {
                $table->unsignedTinyInteger('recruiter_score')->nullable()->after('ats_score');
            }
            if (! Schema::hasColumn('analysis_reports', 'keyword_match_score')) {
                $table->unsignedTinyInteger('keyword_match_score')->nullable()->after('recruiter_score');
            }
            if (! Schema::hasColumn('analysis_reports', 'executive_summary')) {
                $table->text('executive_summary')->nullable()->after('full_analysis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('analysis_reports', function (Blueprint $table) {
            $table->dropColumn(['recruiter_score', 'keyword_match_score', 'executive_summary']);
        });
    }
};
