<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('structure_actions', fn (Blueprint $table) => $table->boolean('is_active')->default(true));
    }

    public function down(): void
    {
        Schema::table('structure_actions', fn (Blueprint $table) => $table->dropColumn('is_active'));
    }
};
