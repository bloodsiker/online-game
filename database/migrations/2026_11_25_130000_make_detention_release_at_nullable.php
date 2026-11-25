<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** release_at = NULL — бессрочное заключение: освобождает только администратор. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_detentions', function (Blueprint $table): void {
            $table->timestamp('release_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('player_detentions', function (Blueprint $table): void {
            $table->timestamp('release_at')->nullable(false)->change();
        });
    }
};
