<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Domain\Enums\QuestType;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use Illuminate\Database\Seeder;

class ClanMentorNpcSeeder extends Seeder
{
    public function run(): void
    {
        $mentor = Npc::updateOrCreate(
            ['name' => config('game.clan_mentor_npc_name')],
            [
                'location_id' => Location::query()->findOrFail(28)->id,
                'hide_location' => false,
                'description' => 'Магистр Рунвальд — хранитель клановых традиций и наставник боевых братств. Он ведёт летописи великих домов, изучает древние знаки силы и знает, какие испытания способны сплотить союзников. У Рунвальда главы кланов могут изучать и улучшать клановые навыки, а воины — получать общие поручения для своего братства.',
                'image' => '/img/npc/flaviy.jpg',
            ],
        );

        Quest::query()
            ->where('type', QuestType::CLAN)
            ->update([
                'start_npc_id' => $mentor->id,
                'complete_npc_id' => $mentor->id,
            ]);
    }
}
