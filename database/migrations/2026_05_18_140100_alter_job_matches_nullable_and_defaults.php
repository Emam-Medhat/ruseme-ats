<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'pgsql'], true)) {
            return;
        }

        Schema::table('job_matches', function (Blueprint $table) {
            $table->integer('match_score')->default(0)->change();
        });

        Schema::table('job_matches', function (Blueprint $table) {
            $table->json('missing_keywords')->nullable()->change();
            $table->json('weak_sentences')->nullable()->change();
            $table->json('improved_sentences')->nullable()->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'pgsql'], true)) {
            return;
        }

        Schema::table('job_matches', function (Blueprint $table) {
            $table->integer('match_score')->nullable(false)->change();
            $table->json('missing_keywords')->nullable(false)->change();
            $table->json('weak_sentences')->nullable(false)->change();
            $table->json('improved_sentences')->nullable(false)->change();
        });
    }
};
