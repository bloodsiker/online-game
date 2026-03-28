<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE share_items MODIFY COLUMN type ENUM(
            'resource','weapon','shield','armor','belt','bag','potion','eat',
            'key','quest','artifact','recipe','chest','scroll','stone',
            'gift','gem','socket_kit','rune','rune_key'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE share_items MODIFY COLUMN type ENUM(
            'resource','weapon','shield','armor','belt','bag','potion','eat',
            'key','quest','artifact','recipe','chest','scroll','stone',
            'gift','gem','socket_kit'
        ) NOT NULL");
    }
};