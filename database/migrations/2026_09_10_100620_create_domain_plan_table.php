<?php

use App\Models\Domain;
use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['domain_id', 'plan_id']);
        });

        $domainId = Domain::query()->where('is_default', true)->value('id')
            ?? Domain::query()->value('id');

        if ($domainId) {
            $now = now();

            Plan::query()->pluck('id')->each(function ($planId) use ($domainId, $now): void {
                Schema::getConnection()->table('domain_plan')->insert([
                    'domain_id' => $domainId,
                    'plan_id' => $planId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_plan');
    }
};
