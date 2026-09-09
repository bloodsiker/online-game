<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NAME = 'Трансгрессор';

    private const DESCRIPTION_PREFIX = 'Трансгрессор для переноса заточки.';

    private const CHANCES = [
        'common' => 30,
        'uncommon' => 45,
        'rare' => 60,
        'epic' => 75,
        'legendary' => 90,
        'heroic' => 100,
    ];

    public function up(): void
    {
        $base = DB::table('share_items')
            ->where('name', self::NAME)
            ->where('type', 'misc')
            ->orderBy('id')
            ->first();

        if ($base === null) {
            return;
        }

        $columns = array_flip(Schema::getColumnListing('share_items'));

        foreach (self::CHANCES as $rarity => $chance) {
            $exists = DB::table('share_items')
                ->where('name', self::NAME)
                ->where('type', 'misc')
                ->where('rarity', $rarity)
                ->exists();

            if ($exists) {
                continue;
            }

            $attributes = array_intersect_key((array) $base, $columns);
            unset($attributes['id']);

            $attributes['rarity'] = $rarity;
            $attributes['description'] = sprintf(
                '%s Шанс успеха — %d%%. При неудаче заточка теряется.',
                self::DESCRIPTION_PREFIX,
                $chance,
            );
            $attributes['created_at'] = now();
            $attributes['updated_at'] = now();

            DB::table('share_items')->insert($attributes);
        }
    }

    public function down(): void
    {
        DB::table('share_items')
            ->where('name', self::NAME)
            ->where('type', 'misc')
            ->where('description', 'like', self::DESCRIPTION_PREFIX.'%')
            ->delete();
    }
};
