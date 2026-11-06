<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('share_recipe_has_items', 'sort_order')) {
            Schema::table('share_recipe_has_items', fn (Blueprint $table) => $table->unsignedInteger('sort_order')->default(0));
        }
    }

    public function down(): void
    {
        Schema::table('share_recipe_has_items', fn (Blueprint $table) => $table->dropColumn('sort_order'));
    }
};
