<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->boolean('does_not_regenerate_hp')->default(false)->after('is_boss');
        });

        DB::table('monsters')->where('is_boss', true)->update(['does_not_regenerate_hp' => true]);
    }

    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn('does_not_regenerate_hp');
        });
    }
};
