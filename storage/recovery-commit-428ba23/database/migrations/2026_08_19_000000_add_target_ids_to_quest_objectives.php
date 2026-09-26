<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_objectives', function (Blueprint $table) {
            $table->json('target_ids')->nullable()->after('target_id');
        });
    }

    public function down(): void
    {
        Schema::table('quest_objectives', function (Blueprint $table) {
            $table->dropColumn('target_ids');
        });
    }
};
