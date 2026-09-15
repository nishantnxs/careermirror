<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('headline')->nullable()->after('phone');
            $table->text('summary')->nullable()->after('headline');
            $table->string('location')->nullable()->after('summary');
            $table->string('current_title')->nullable()->after('location');
            $table->unsignedTinyInteger('years_of_experience')->nullable()->after('current_title');
            $table->string('website')->nullable()->after('years_of_experience');
            $table->string('linkedin_url')->nullable()->after('website');
            $table->date('date_of_birth')->nullable()->after('linkedin_url');
            $table->string('preferred_job_type', 50)->nullable()->after('date_of_birth');
            $table->decimal('expected_salary_min', 12, 2)->nullable()->after('preferred_job_type');
            $table->decimal('expected_salary_max', 12, 2)->nullable()->after('expected_salary_min');
            $table->string('currency', 3)->default('INR')->after('expected_salary_max');
            $table->string('avatar_path')->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'headline',
                'summary',
                'location',
                'current_title',
                'years_of_experience',
                'website',
                'linkedin_url',
                'date_of_birth',
                'preferred_job_type',
                'expected_salary_min',
                'expected_salary_max',
                'currency',
                'avatar_path',
            ]);
        });
    }
};
