<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('resume_id')->constrained()->onDelete('cascade');
            $table->string('job_title');
            $table->text('job_description');
            $table->integer('match_score');
            $table->json('missing_keywords');
            $table->json('weak_sentences');
            $table->json('improved_sentences');
            $table->json('ai_raw_response')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'resume_id', 'job_title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};
