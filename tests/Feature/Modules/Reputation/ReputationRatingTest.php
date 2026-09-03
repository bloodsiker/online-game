<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Reputation;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReputationRatingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->float('experience_multiplier')->default(1);
            $table->unsignedBigInteger('reputation_rating')->default(0);
            $table->timestamps();
        });

        Schema::create('reputations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('reputation_tiers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reputation_id');
            $table->unsignedInteger('min_points');
            $table->string('medal_name')->nullable();
            $table->string('medal_icon')->nullable();
            $table->unsignedBigInteger('feat_quest_id')->nullable();
            $table->string('feat_medal_name')->nullable();
            $table->string('feat_medal_icon')->nullable();
            $table->timestamps();
        });

        Schema::create('player_reputations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('reputation_id');
            $table->unsignedInteger('points')->default(0);
            $table->timestamp('last_completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quest_players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('quest_id');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function test_regular_medals_use_configured_rating_values(): void
    {
        $this->assertSame(10, $this->tierAt(500)->regularMedalRating());
        $this->assertSame(20, $this->tierAt(1000)->regularMedalRating());
        $this->assertSame(50, $this->tierAt(2000)->regularMedalRating());
        $this->assertSame(100, $this->tierAt(3000)->regularMedalRating());
        $this->assertSame(0, $this->tierAt(4000)->regularMedalRating());
    }

    public function test_rating_is_recalculated_without_duplicate_awards(): void
    {
        $player = Player::query()->create();
        $reputation = Reputation::query()->create(['name' => 'Test reputation']);

        foreach ([500, 1000, 2000] as $points) {
            $reputation->tiers()->create([
                'min_points' => $points,
                'medal_name' => 'Medal '.$points,
            ]);
        }

        $reputation->tiers()->create([
            'min_points' => 3000,
            'medal_name' => 'Medal 3000',
            'feat_quest_id' => 501,
            'feat_medal_name' => 'Red medal',
        ]);

        PlayerReputation::query()->create([
            'player_id' => $player->id,
            'reputation_id' => $reputation->id,
            'points' => 3000,
        ]);

        $service = new ReputationService;

        $this->assertSame(180, $service->syncReputationRating($player));

        DB::table('quest_players')->insert([
            'player_id' => $player->id,
            'quest_id' => 501,
            'status' => QuestPlayerStatus::COMPLETED->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(480, $service->syncReputationRating($player));
        $this->assertSame(480, $service->syncReputationRating($player));
        $this->assertSame(480, (int) $player->fresh()->reputation_rating);
    }

    private function tierAt(int $points): ReputationTier
    {
        return (new ReputationTier)->forceFill([
            'min_points' => $points,
            'medal_name' => 'Medal '.$points,
        ]);
    }
}
