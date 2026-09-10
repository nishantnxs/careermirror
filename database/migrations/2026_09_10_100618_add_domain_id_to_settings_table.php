<?php

use App\Enums\DomainStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('domain_id')->nullable()->after('id');
        });

        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $host = Str::lower((string) (parse_url($appUrl, PHP_URL_HOST) ?: 'localhost'));
        $siteName = DB::table('settings')->where('key', 'site_name')->value('value')
            ?: config('app.name', 'CareerMirror');

        $domainId = DB::table('domains')->insertGetId([
            'name' => $host,
            'host' => $host,
            'url' => $appUrl,
            'website_name' => $siteName,
            'status' => DomainStatus::Active->value,
            'is_default' => true,
            'seo_title' => DB::table('settings')->where('key', 'seo_title')->value('value'),
            'seo_description' => DB::table('settings')->where('key', 'seo_description')->value('value'),
            'seo_keywords' => DB::table('settings')->where('key', 'seo_keywords')->value('value'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')->update(['domain_id' => $domainId]);

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->foreign('domain_id')->references('id')->on('domains')->cascadeOnDelete();
            $table->unique(['domain_id', 'key']);
        });
    }

    public function down(): void
    {
        $defaultId = DB::table('domains')->where('is_default', true)->value('id')
            ?? DB::table('domains')->value('id');

        if ($defaultId) {
            DB::table('settings')->where('domain_id', '!=', $defaultId)->delete();
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['domain_id', 'key']);
            $table->dropForeign(['domain_id']);
            $table->dropColumn('domain_id');
            $table->unique('key');
        });
    }
};
