<?php

namespace Database\Seeders;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayerObjective;
use Illuminate\Database\Seeder;

/**
 * Одноразовая миграция данных для существующей БД: удаляет ВСЕ квесты
 * «[Орден] …» (вместе с прогрессом игроков по ним) и запускает
 * ReputationSeeder, который создаёт актуальное наполнение Ордена Охотников
 * (НПС «Мудрый Финко» на локации 305, цели Мантикора(34)+, collect-трофеи,
 * подвиги для медалей).
 *
 * ВНИМАНИЕ: повторный запуск сбрасывает прогресс игроков по квестам Ордена.
 * На чистой установке не нужен — достаточно ReputationSeeder.
 */
class OrdenHuntersReworkSeeder extends Seeder
{
    public function run(): void
    {
        $this->deleteOldQuests();
        $this->call(ReputationSeeder::class);
    }

    private function deleteOldQuests(): void
    {
        $oldQuests = Quest::where('title', 'like', '[Орден]%')->get();

        foreach ($oldQuests as $quest) {
            $questPlayerIds = QuestPlayer::where('quest_id', $quest->id)->pluck('id');
            QuestPlayerObjective::whereIn('quest_player_id', $questPlayerIds)->delete();
            QuestPlayer::where('quest_id', $quest->id)->delete();
        }

        // Сначала рвём self-FK цепочек, затем удаляем
        // (objectives/rewards/tier-links удаляются каскадом)
        Quest::where('title', 'like', '[Орден]%')->update(['after_quest_id' => null, 'parent_quest_id' => null]);
        foreach ($oldQuests as $quest) {
            $quest->delete();
        }

        $this->command->info('  ├ удалено старых квестов Ордена: '.$oldQuests->count());
    }
}
