<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing full admins created before RBAC had is_super_admin=false by default.
        DB::table('admins')
            ->whereNull('admin_role_id')
            ->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        // Irreversible data fix — leave flags as-is.
    }
};
