<?php

declare(strict_types=1);

use App\Modules\Share\Domain\Enums\ShareItemType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, ShareItemType> */
    private const PROFESSION_TYPES = [
        'Рыбак' => ShareItemType::FISH,
        'Геолог' => ShareItemType::PRECIOUS_GEM,
        'Травник' => ShareItemType::PLANT,
        'Лесоруб' => ShareItemType::WOOD,
    ];

    public function up(): void
    {
        $this->extendItemTypes();

        foreach (self::PROFESSION_TYPES as $profession => $type) {
            $skillId = DB::table('skills')->where('name', $profession)->value('id');

            if ($skillId === null) {
                continue;
            }

            DB::table('share_items')
                ->where('skill_id', $skillId)
                ->where('type', ShareItemType::RESOURCE->value)
                ->update([
                    'type' => $type->value,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        foreach (self::PROFESSION_TYPES as $profession => $type) {
            $skillId = DB::table('skills')->where('name', $profession)->value('id');

            if ($skillId === null) {
                continue;
            }

            DB::table('share_items')
                ->where('skill_id', $skillId)
                ->where('type', $type->value)
                ->update([
                    'type' => ShareItemType::RESOURCE->value,
                    'updated_at' => now(),
                ]);
        }

        $this->restoreItemTypes();
    }

    private function extendItemTypes(): void
    {
        DB::statement("ALTER TABLE `share_items` MODIFY `type` ENUM(
            'resource', 'fish', 'precious_gem', 'plant', 'wood',
            'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat',
            'key', 'quest', 'artifact', 'recipe', 'chest', 'scroll',
            'stone', 'gem', 'mount', 'rune', 'rune_key', 'misc', 'book', 'tool'
        ) NULL DEFAULT 'resource'");
    }

    private function restoreItemTypes(): void
    {
        DB::statement("ALTER TABLE `share_items` MODIFY `type` ENUM(
            'resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat',
            'key', 'quest', 'artifact', 'recipe', 'chest', 'scroll',
            'stone', 'gem', 'mount', 'rune', 'rune_key', 'misc', 'book', 'tool'
        ) NULL DEFAULT 'resource'");
    }
};
