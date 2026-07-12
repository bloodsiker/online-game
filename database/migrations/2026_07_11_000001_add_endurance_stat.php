<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->float('endurance', 2)->default(1)->after('intelligence');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->float('endurance', 2)->default(1)->after('intelligence');
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('endurance');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('endurance');
        });
    }
};
