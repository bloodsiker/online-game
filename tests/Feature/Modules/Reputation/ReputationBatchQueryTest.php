<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Reputation;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReputationBatchQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('quest_players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('quest_id');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function test_earned_medals_check_all_feat_quests_with_one_query(): void
    {
        $tiers = new EloquentCollection;

        for ($id = 1; $id <= 5; $id++) {
            $tiers->push((new ReputationTier)->forceFill([
                'id' => $id,
                'min_points' => $id * 10,
                'medal_name' => 'Medal '.$id,
                'feat_quest_id' => 100 + $id,
            ]));

            if ($id % 2 === 1) {
                DB::table('quest_players')->insert([
                    'player_id' => 10,
                    'quest_id' => 100 + $id,
                    'status' => QuestPlayerStatus::COMPLETED->value,
                ]);
            }
        }

        $reputation = new Reputation;
        $reputation->setRelation('tiers', $tiers);

        $player = (new Player)->forceFill(['id' => 10]);
        $player->exists = true;

        DB::flushQueryLog();
        DB::enableQueryLog();

        $medals = (new ReputationService)->getEarnedMedals($reputation, 100, $player);

        $questProgressQueries = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains(strtolower($query), 'quest_players'));

        $this->assertSame([1, 3, 5], $medals->pluck('id')->all());
        $this->assertCount(1, $questProgressQueries);
    }

    public function test_separate_feat_medal_does_not_block_the_regular_tier_medal(): void
    {
        $tier = (new ReputationTier)->forceFill([
            'id' => 1,
            'min_points' => 3000,
            'medal_name' => 'Медаль Почета',
            'feat_quest_id' => 501,
            'feat_medal_name' => 'Медаль Поклонения',
        ]);

        $reputation = new Reputation;
        $reputation->setRelation('tiers', new EloquentCollection([$tier]));

        $player = (new Player)->forceFill(['id' => 10]);
        $player->exists = true;

        $service = new ReputationService;

        $this->assertSame([1], $service->getEarnedMedals($reputation, 3000, $player)->pluck('id')->all());
        $this->assertSame([], $service->getEarnedFeatMedals($reputation, 3000, $player)->pluck('id')->all());

        DB::table('quest_players')->insert([
            'player_id' => 10,
            'quest_id' => 501,
            'status' => QuestPlayerStatus::COMPLETED->value,
        ]);

        $this->assertSame([1], $service->getEarnedMedals($reputation, 3000, $player)->pluck('id')->all());
        $this->assertSame([1], $service->getEarnedFeatMedals($reputation, 3000, $player)->pluck('id')->all());
    }
}
