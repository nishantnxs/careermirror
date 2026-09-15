<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('candidate_resume_id')->nullable()->constrained('candidate_resumes')->nullOnDelete();
            $table->text('cover_letter')->nullable();
            $table->string('status', 40)->default('applied')->index();
            $table->timestamp('applied_at');
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->unique(['candidate_id', 'job_posting_id']);
            $table->index(['candidate_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
