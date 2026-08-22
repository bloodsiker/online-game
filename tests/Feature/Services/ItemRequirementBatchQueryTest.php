<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Services\ItemRequirementService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ItemRequirementBatchQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('share_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('type');
            $table->string('stat_key')->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->unsignedInteger('min_value');
            $table->timestamps();
        });
        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->unsignedInteger('lvl');
            $table->timestamps();
        });
    }

    public function test_all_skill_requirements_are_checked_with_one_player_skills_query(): void
    {
        DB::table('share_items')->insert(['id' => 1, 'name' => 'Test item']);

        for ($skillId = 1; $skillId <= 5; $skillId++) {
            DB::table('skills')->insert(['id' => $skillId, 'name' => 'Skill '.$skillId]);
            DB::table('share_item_requirements')->insert([
                'share_item_id' => 1,
                'type' => ShareItemRequirementType::SKILL->value,
                'skill_id' => $skillId,
                'min_value' => 3,
            ]);
            DB::table('player_skills')->insert([
                'player_id' => 10,
                'skill_id' => $skillId,
                'lvl' => 3,
            ]);
        }

        $player = (new Player)->forceFill(['id' => 10]);
        $player->exists = true;

        DB::flushQueryLog();
        DB::enableQueryLog();

        $error = (new ItemRequirementService)->check($player, ShareItem::findOrFail(1));

        $playerSkillQueries = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains(strtolower($query), 'player_skills'));

        $this->assertNull($error);
        $this->assertCount(1, $playerSkillQueries);
    }
}
