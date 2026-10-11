<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropIndex(['is_lockpick']);
            $table->dropColumn('is_lockpick');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->boolean('is_lockpick')->default(false)->after('skill_exp')->index();
        });
    }
};
