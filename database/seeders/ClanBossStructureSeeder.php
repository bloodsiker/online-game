<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClanBossStructureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $location = Location::query()->findOrFail(28);
            $structure = Structure::query()
                ->where('location_id', $location->id)
                ->where('type', Structure::TYPE_CLAN_BOSS)
                ->first() ?? new Structure;

            if (! $structure->exists) {
                $structure->location_id = $location->id;
                $structure->type = Structure::TYPE_CLAN_BOSS;
                $structure->setTranslation('name', 'ru', 'Логово клановых боссов');
                $structure->setTranslation('name', 'uk', 'Лігво кланових босів');
                $structure->setTranslation('description', 'ru', 'Место, где кланы сообща сражаются с могущественными противниками.');
                $structure->setTranslation('description', 'uk', 'Місце, де клани спільно б’ються з могутніми супротивниками.');
                $structure->save();
            }
        });
    }
}
