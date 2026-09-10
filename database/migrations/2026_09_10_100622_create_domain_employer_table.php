<?php

use App\Models\Domain;
use App\Models\Employer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_employer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->foreignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['domain_id', 'employer_id']);
        });

        $domainId = Domain::query()->where('is_default', true)->value('id')
            ?? Domain::query()->value('id');

        if ($domainId) {
            $now = now();

            Employer::query()->pluck('id')->each(function ($employerId) use ($domainId, $now): void {
                Schema::getConnection()->table('domain_employer')->insert([
                    'domain_id' => $domainId,
                    'employer_id' => $employerId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_employer');
    }
};
