<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            if (! Schema::hasColumn('resumes', 'filename')) {
                $table->string('filename')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('resumes', 'file_path')) {
                $table->string('file_path')->nullable()->after('filename');
            }
            if (! Schema::hasColumn('resumes', 'status')) {
                $table->string('status', 32)->default('completed')->after('file_path');
            }
        });

        if (Schema::hasColumn('resumes', 'filename')) {
            DB::table('resumes')
                ->whereNull('filename')
                ->update([
                    'filename' => DB::raw("COALESCE(title, 'resume.pdf')"),
                    'file_path' => DB::raw('original_file_path'),
                    'status' => 'completed',
                ]);
        }

        Schema::table('analysis_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('analysis_reports', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('resume_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('analysis_reports', 'ats_score')) {
                $table->unsignedTinyInteger('ats_score')->nullable()->after('overall_score');
            }
            if (! Schema::hasColumn('analysis_reports', 'full_analysis')) {
                $table->json('full_analysis')->nullable()->after('style_score');
            }
        });

        if (Schema::hasColumn('analysis_reports', 'user_id')) {
            DB::table('analysis_reports')
                ->whereNull('user_id')
                ->orderBy('id')
                ->chunkById(100, function ($reports) {
                    foreach ($reports as $report) {
                        $userId = DB::table('resumes')->where('id', $report->resume_id)->value('user_id');
                        if ($userId) {
                            DB::table('analysis_reports')->where('id', $report->id)->update(['user_id' => $userId]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('analysis_reports', function (Blueprint $table) {
            if (Schema::hasColumn('analysis_reports', 'full_analysis')) {
                $table->dropColumn('full_analysis');
            }
            if (Schema::hasColumn('analysis_reports', 'ats_score')) {
                $table->dropColumn('ats_score');
            }
            if (Schema::hasColumn('analysis_reports', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('resumes', function (Blueprint $table) {
            if (Schema::hasColumn('resumes', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('resumes', 'file_path')) {
                $table->dropColumn('file_path');
            }
            if (Schema::hasColumn('resumes', 'filename')) {
                $table->dropColumn('filename');
            }
        });
    }
};
