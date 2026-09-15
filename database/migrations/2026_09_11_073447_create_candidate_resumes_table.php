<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('title');
            $table->string('source', 20);
            $table->boolean('is_default')->default(false)->index();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->json('content')->nullable();
            $table->timestamps();

            $table->index(['candidate_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_resumes');
    }
};
