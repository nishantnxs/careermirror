<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('plan_type')->default('paid')->index();
            $table->text('description')->nullable();

            $table->unsignedInteger('duration_value')->default(1);
            $table->string('duration_unit')->default('month');

            $table->string('currency', 3)->default('INR');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->nullable();

            $table->unsignedInteger('trial_days')->default(0);
            $table->json('features')->nullable();

            $table->date('expiry_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
