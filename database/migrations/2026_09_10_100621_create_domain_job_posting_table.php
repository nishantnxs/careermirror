<?php

use App\Models\Domain;
use App\Models\JobPosting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_job_posting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['domain_id', 'job_posting_id']);
        });

        $domainId = Domain::query()->where('is_default', true)->value('id')
            ?? Domain::query()->value('id');

        if ($domainId) {
            $now = now();

            JobPosting::query()->pluck('id')->each(function ($jobId) use ($domainId, $now): void {
                Schema::getConnection()->table('domain_job_posting')->insert([
                    'domain_id' => $domainId,
                    'job_posting_id' => $jobId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_job_posting');
    }
};
