<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_reputations', function (Blueprint $table): void {
            $table->timestamp('last_offering_at')->nullable()->after('last_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('player_reputations', function (Blueprint $table): void {
            $table->dropColumn('last_offering_at');
        });
    }
};
