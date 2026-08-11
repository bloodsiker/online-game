<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table) {
            $table->boolean('is_give')->default(true)->after('is_sell');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table) {
            $table->dropColumn('is_give');
        });
    }
};