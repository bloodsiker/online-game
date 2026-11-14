<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Стиль физической атаки монстра — разнообразит фразы в логе боя. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->string('attack_style', 16)->default('strike')->after('attack_type');
        });
    }

    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn('attack_style');
        });
    }
};
