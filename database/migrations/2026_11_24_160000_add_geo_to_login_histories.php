<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_histories', function (Blueprint $table): void {
            $table->string('country_code', 2)->nullable()->after('ip_address');
            $table->string('country', 80)->nullable()->after('country_code');
            $table->string('region', 100)->nullable()->after('country');
            $table->string('city', 100)->nullable()->after('region');
            $table->index('country_code', 'login_histories_country_index');
        });
    }

    public function down(): void
    {
        Schema::table('login_histories', function (Blueprint $table): void {
            $table->dropIndex('login_histories_country_index');
            $table->dropColumn(['country_code', 'country', 'region', 'city']);
        });
    }
};
