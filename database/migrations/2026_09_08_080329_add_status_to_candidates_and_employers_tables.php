<?php

use App\Enums\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->convertTable('candidates');
        $this->convertTable('employers');
    }

    public function down(): void
    {
        $this->revertTable('candidates');
        $this->revertTable('employers');
    }

    protected function convertTable(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'status')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('status')->default(AccountStatus::Active->value)->after('password');
            });
        }

        if (Schema::hasColumn($table, 'is_active')) {
            DB::table($table)->where('is_active', false)->update(['status' => AccountStatus::Inactive->value]);
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('is_active');
            });
        }
    }

    protected function revertTable(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status')) {
            return;
        }

        if (! Schema::hasColumn($table, 'is_active')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->boolean('is_active')->default(true)->after('password');
            });
        }

        DB::table($table)->where('status', '!=', AccountStatus::Active->value)->update(['is_active' => false]);

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('status');
        });
    }
};
