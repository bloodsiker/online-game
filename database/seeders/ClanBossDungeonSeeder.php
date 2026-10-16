<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Dungeon\Domain\Enums\DungeonCooldownType;
use App\Modules\Dungeon\Domain\Enums\DungeonStageCompletionType;
use App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType;
use App\Modules\Dungeon\Domain\Enums\DungeonType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClanBossDungeonSeeder extends Seeder
{
    private const DUNGEON_NAME = 'Клановое логово';

    /** @var array<int, array<string, mixed>> */
    private const BOSSES = [
        1 => [
            'name_ru' => 'Каменный Страж',
            'name_uk' => 'Кам’яний Вартовий',
            'description_ru' => 'Древний хранитель, пробуждающийся, когда клан бросает вызов логову.',
            'description_uk' => 'Стародавній охоронець, що прокидається, коли клан кидає виклик лігву.',
            'level' => 30,
            'hp' => 6000,
            'armor' => 120,
            'dodge' => 12,
            'critical' => 10,
            'min_damage' => 30,
            'max_damage' => 45,
            'experience' => 1000,
            'gold_budget' => 27000,
        ],
        2 => [
            'name_ru' => 'Пепельный Колосс',
            'name_uk' => 'Попелястий Колос',
            'description_ru' => 'Гигант из пепла и раскалённого камня — последний защитник кланового логова.',
            'description_uk' => 'Велетень із попелу й розпеченого каменю — останній захисник кланового лігва.',
            'level' => 35,
            'hp' => 6000,
            'armor' => 150,
            'dodge' => 10,
            'critical' => 12,
            'min_damage' => 40,
            'max_damage' => 55,
            'experience' => 1500,
            'gold_budget' => 36000,
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $dungeon = LocalizedContentSeederQuery::firstOrNewFor(Dungeon::class, [
                'name' => self::DUNGEON_NAME,
            ]);
            $dungeon->forceFill([
                'description' => [
                    'ru' => 'Асинхронный клановый поход: соклановцы сообща сражаются с боссами по очереди. На весь поход отводится 72 часа.',
                    'uk' => 'Асинхронний клановий похід: члени клану спільно б’ються з босами по черзі. На весь похід відведено 72 години.',
                ],
                'tier' => 1,
                'type' => DungeonType::TOWER,
                'max_players' => 5,
                'cooldown_type' => DungeonCooldownType::PERSONAL,
                'cooldown_seconds' => 0,
                'time_limit_seconds' => 259200,
                'min_level' => 1,
                'is_active' => true,
                'is_clan_boss' => true,
                'min_clan_days' => 3,
                'min_damage' => 0,
                'clan_boss_hit_batch_size' => 10,
                'clan_boss_hit_batch_cooldown_seconds' => 21600,
            ])->save();

            foreach (self::BOSSES as $stageNumber => $bossData) {
                $boss = LocalizedContentSeederQuery::firstOrNewFor(Monster::class, [
                    'name' => $bossData['name_ru'],
                ]);
                $boss->forceFill([
                    'name' => [
                        'ru' => $bossData['name_ru'],
                        'uk' => $bossData['name_uk'],
                    ],
                    'description' => [
                        'ru' => $bossData['description_ru'],
                        'uk' => $bossData['description_uk'],
                    ],
                    'lvl' => $bossData['level'],
                    'hp' => $bossData['hp'],
                    'armor' => $bossData['armor'],
                    'dodge' => $bossData['dodge'],
                    'critical' => $bossData['critical'],
                    'min_dmg' => $bossData['min_damage'],
                    'max_dmg' => $bossData['max_damage'],
                    'aggression' => 0,
                    'exp' => $bossData['experience'],
                    'min_money' => 0,
                    'max_money' => 0,
                    'is_boss' => true,
                    'does_not_regenerate_hp' => true,
                ])->save();

                $stage = DungeonStage::query()->firstOrNew([
                    'dungeon_id' => $dungeon->id,
                    'number' => $stageNumber,
                ]);
                $stage->forceFill([
                    'name' => [
                        'ru' => "Босс {$stageNumber}: {$bossData['name_ru']}",
                        'uk' => "Бос {$stageNumber}: {$bossData['name_uk']}",
                    ],
                    'time_limit_seconds' => null,
                    'completion_type' => DungeonStageCompletionType::KILL_ALL,
                    'spawn_type' => DungeonStageSpawnType::FIXED,
                    'total_monsters' => 1,
                    'max_hits_per_user' => null,
                    'personal_cooldown_seconds' => null,
                    'clan_cooldown_seconds' => null,
                    'gold_budget' => $bossData['gold_budget'],
                ])->save();

                $stage->monsters()->delete();
                $stage->monsters()->create([
                    'monster_id' => $boss->id,
                    'quantity' => 1,
                    'weight' => 1,
                ]);
            }

            $structure = Structure::query()
                ->where('location_id', 28)
                ->where('type', Structure::TYPE_CLAN_BOSS)
                ->orderBy('id')
                ->first();

            if ($structure === null) {
                $location = Location::query()->findOrFail(28);
                $structure = new Structure;
                $structure->location_id = $location->id;
                $structure->type = Structure::TYPE_CLAN_BOSS;
                $structure->setTranslation('name', 'ru', 'Логово клановых боссов');
                $structure->setTranslation('name', 'uk', 'Лігво кланових босів');
                $structure->setTranslation('description', 'ru', 'Место, где кланы сообща сражаются с могущественными противниками.');
                $structure->setTranslation('description', 'uk', 'Місце, де клани спільно б’ються з могутніми супротивниками.');
            }

            $structure->dungeon_id = $dungeon->id;
            $structure->save();
        });

        $this->command?->info('ClanBossDungeonSeeder: клановый данж и два босса настроены.');
    }
}
