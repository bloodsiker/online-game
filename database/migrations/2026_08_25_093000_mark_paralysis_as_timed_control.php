<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('effects')
            ->where('slug', 'paralysis')
            ->update(['active_type' => 'paralysis']);
    }

    public function down(): void
    {
        DB::table('effects')
            ->where('slug', 'paralysis')
            ->where('active_type', 'paralysis')
            ->update(['active_type' => null]);
    }
};
