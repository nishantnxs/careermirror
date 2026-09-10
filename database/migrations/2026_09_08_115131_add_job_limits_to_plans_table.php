<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('jobs_allowed')->default(1)->after('trial_days');
            $table->unsignedInteger('job_duration_value')->default(30)->after('jobs_allowed');
            $table->string('job_duration_unit')->default('day')->after('job_duration_value');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['jobs_allowed', 'job_duration_value', 'job_duration_unit']);
        });
    }
};
