<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('job_posting_id')->nullable()->constrained('job_postings')->nullOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained('job_applications')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('employer_last_read_at')->nullable();
            $table->timestamp('candidate_last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['employer_id', 'candidate_id']);
            $table->index(['candidate_id', 'last_message_at']);
            $table->index(['employer_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
