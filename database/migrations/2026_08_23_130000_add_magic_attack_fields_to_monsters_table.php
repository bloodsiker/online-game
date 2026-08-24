<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->string('attack_type', 16)->default('physical')->after('max_dmg');
            $table->integer('magic_attack')->default(0)->after('attack_type');
            $table->float('magic_power_coefficient')->default(0.0)->after('magic_attack');
            $table->integer('magic_resistance')->default(0)->after('magic_power_coefficient');
        });
    }

    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn(['attack_type', 'magic_attack', 'magic_power_coefficient', 'magic_resistance']);
        });
    }
};
