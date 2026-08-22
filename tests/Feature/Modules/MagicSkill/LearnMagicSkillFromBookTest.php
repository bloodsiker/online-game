<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\MagicSkill\Application\UseCases\LearnMagicSkillFromBook;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ItemRequirementService;
use App\Services\MagicSkillRequirementService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LearnMagicSkillFromBookTest extends TestCase
{
    private const SHARE_ITEM_ID = 100;

    private const MAGIC_SKILL_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();

        DB::table('races')->insert(['id' => 1, 'name' => 'Человек']);
        DB::table('players')->insert([
            'id' => 1,
            'user_id' => 1,
            'race_id' => 1,
            'lvl' => 10,
            'strength' => 10,
            'agility' => 10,
            'intuition' => 10,
            'wisdom' => 10,
            'intelligence' => 10,
        ]);
        DB::table('users')->insert([
            'id' => 1,
            'player_id' => 1,
            'name' => 'Игрок',
            'email' => 'player@example.test',
            'password' => 'password',
        ]);

        DB::table('share_items')->insert([
            'id' => self::SHARE_ITEM_ID,
            'type' => 'book',
            'name' => 'Книга: Огненный шар',
        ]);
        DB::table('magic_skills')->insert([
            'id' => self::MAGIC_SKILL_ID,
            'name' => 'Огненный шар',
            'slug' => 'fireball',
            'type' => 'attack',
            'target_type' => 'enemy',
            'is_passive' => false,
            'mana_cost' => 10,
            'cooldown' => 5,
            'level' => 1,
        ]);
        DB::table('magic_skill_books')->insert([
            'share_item_id' => self::SHARE_ITEM_ID,
            'magic_skill_id' => self::MAGIC_SKILL_ID,
        ]);
    }

    public function test_learning_succeeds_when_requirements_met_and_book_is_consumed(): void
    {
        // Требование: уровень >= 5, у игрока уровень 10 — выполнено.
        DB::table('magic_skill_requirements')->insert([
            'magic_skill_id' => self::MAGIC_SKILL_ID,
            'type' => 'level',
            'min_value' => 5,
        ]);

        $itemId = $this->giveBook(count: 1);

        $useCase = $this->makeUseCase();
        $user = User::findOrFail(1);

        $result = $useCase->execute($user, self::SHARE_ITEM_ID);

        $this->assertTrue($result->ok);
        $this->assertTrue(
            DB::table('player_magic_skills')
                ->where('player_id', 1)
                ->where('magic_skill_id', self::MAGIC_SKILL_ID)
                ->exists(),
            'player_magic_skills row must be created'
        );
        $this->assertFalse(
            DB::table('backpacks')->where('item_id', $itemId)->exists(),
            'the single book must be consumed and its backpack row removed'
        );
    }

    public function test_learning_fails_and_book_is_not_consumed_when_requirement_unmet(): void
    {
        // Требование: уровень >= 50, у игрока уровень 10 — не выполнено.
        DB::table('magic_skill_requirements')->insert([
            'magic_skill_id' => self::MAGIC_SKILL_ID,
            'type' => 'level',
            'min_value' => 50,
        ]);

        $itemId = $this->giveBook(count: 1);

        $useCase = $this->makeUseCase();
        $user = User::findOrFail(1);

        $result = $useCase->execute($user, self::SHARE_ITEM_ID);

        $this->assertFalse($result->ok);
        $this->assertFalse(
            DB::table('player_magic_skills')
                ->where('player_id', 1)
                ->where('magic_skill_id', self::MAGIC_SKILL_ID)
                ->exists(),
            'no player_magic_skills row must be created when requirement is unmet'
        );
        $this->assertSame(
            1,
            DB::table('backpacks')->where('item_id', $itemId)->value('count'),
            'the book must not be consumed when the requirement is unmet'
        );
    }

    public function test_learning_twice_is_rejected_on_the_second_attempt(): void
    {
        // Требование: уровень >= 5, у игрока уровень 10 — выполнено оба раза.
        DB::table('magic_skill_requirements')->insert([
            'magic_skill_id' => self::MAGIC_SKILL_ID,
            'type' => 'level',
            'min_value' => 5,
        ]);

        // Две копии книги в одной стакающейся ячейке рюкзака.
        $itemId = $this->giveBook(count: 2);

        $useCase = $this->makeUseCase();
        $user = User::findOrFail(1);

        $first = $useCase->execute($user, self::SHARE_ITEM_ID);
        $this->assertTrue($first->ok);
        $this->assertSame(
            1,
            DB::table('backpacks')->where('item_id', $itemId)->value('count'),
            'first attempt must consume exactly one copy of the book'
        );

        $second = $useCase->execute($user, self::SHARE_ITEM_ID);

        $this->assertFalse($second->ok);
        $this->assertStringContainsString('уже изучено', $second->message);
        $this->assertSame(
            1,
            DB::table('backpacks')->where('item_id', $itemId)->value('count'),
            'the second copy of the book must not be consumed on the rejected second attempt'
        );
        $this->assertSame(
            1,
            DB::table('player_magic_skills')
                ->where('player_id', 1)
                ->where('magic_skill_id', self::MAGIC_SKILL_ID)
                ->count(),
            'still only one player_magic_skills row after the rejected second attempt'
        );
    }

    private function giveBook(int $count): int
    {
        $itemId = DB::table('items')->insertGetId([
            'share_item_id' => self::SHARE_ITEM_ID,
        ]);

        DB::table('backpacks')->insert([
            'user_id' => 1,
            'item_id' => $itemId,
            'equipped' => 0,
            'count' => $count,
            'sort_order' => 0,
        ]);

        return (int) $itemId;
    }

    private function makeUseCase(): LearnMagicSkillFromBook
    {
        return new LearnMagicSkillFromBook(
            requirementService: new MagicSkillRequirementService,
            backpackService: new BackpackService($this->createMock(ItemRequirementService::class)),
        );
    }

    private function createTables(): void
    {
        Schema::create('races', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('race_id');
            $table->integer('lvl')->default(1);
            $table->float('strength')->default(1);
            $table->float('agility')->default(1);
            $table->float('intuition')->default(1);
            $table->float('wisdom')->default(1);
            $table->float('intelligence')->default(1);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamp('last_online_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('resource');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('count_use')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_slot_usable')->default(false);
            $table->boolean('is_weight')->default(true);
            $table->integer('price')->default(0);
            $table->timestamps();
        });

        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id');
            $table->integer('percent')->default(60);
            $table->timestamps();
        });

        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count')->default(1);
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('upgrade_lvl')->default(0);
            $table->unsignedSmallInteger('upgrade_pity')->default(0);
            $table->unsignedSmallInteger('upgrade_fail_streak')->default(0);
            $table->integer('additional_attack')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_open')->default(false);
            $table->unsignedTinyInteger('socket_count')->default(0);
            $table->timestamps();
        });

        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped');
            $table->integer('count')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->string('slug')->nullable();
            $table->string('type')->default('attack');
            $table->string('target_type')->default('enemy');
            $table->boolean('is_passive')->default(false);
            $table->integer('mana_cost')->default(0);
            $table->integer('min_damage')->default(0);
            $table->integer('max_damage')->default(0);
            $table->float('power_coefficient')->default(0);
            $table->integer('cooldown')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->integer('base_healing')->default(0);
            $table->timestamps();
        });

        // Требуется MagicSkillRequirementService::check(), которое безусловно
        // делает $skill->loadMissing('requirements.skill') — даже когда среди
        // требований нет ни одного типа SKILL, запрос по таблице всё равно уходит.
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
        });

        Schema::create('magic_skill_books', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedBigInteger('magic_skill_id')->unique();
            $table->timestamps();
        });

        Schema::create('magic_skill_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->string('type', 20);
            $table->string('stat_key', 20)->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->unsignedSmallInteger('min_value');
            $table->timestamps();
        });

        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->unique(['player_id', 'magic_skill_id']);
        });
    }
}
