<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;

class ClanSkillHallSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::query()->findOrFail(28);
        $mentor = Npc::query()->where('name', config('game.clan_mentor_npc_name'))->first();

        Structure::query()->updateOrCreate(
            [
                'type' => Structure::TYPE_CLAN_SKILL_HALL,
                'location_id' => $location->id,
            ],
            [
                'name' => 'Зал клановых наставлений',
                'npc_id' => $mentor?->id,
            ],
        );
    }
}
