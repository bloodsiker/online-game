<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Item;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Item\Application\Services\LockpickingService;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Domain\Services\PeacefulProfessionExperienceService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class LockpickingFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();
        $this->seedPlayer();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_start_is_blocked_for_dead_player_and_active_battle(): void
    {
        $service = $this->service();
        DB::table('players')->where('id', 1)->update(['hp_now' => 0]);

        $dead = $service->start(User::query()->findOrFail(1), 100);
        $this->assertFalse($dead->ok);
        $this->assertSame(422, $dead->httpCode);

        DB::table('players')->where('id', 1)->update(['hp_now' => 100]);
        DB::table('battles')->insert(['id' => 1, 'location_id' => 1, 'status' => 1]);
        DB::table('battle_details')->insert(['battle_id' => 1, 'user_id' => 1, 'status' => 1]);

        $battle = $service->start(User::query()->findOrFail(1), 100);
        $this->assertFalse($battle->ok);
        $this->assertSame(422, $battle->httpCode);
    }

    public function test_start_early_complete_and_cancel_flow(): void
    {
        Carbon::setTestNow('2026-09-15 12:00:00');
        $this->seedLockedChestAndLockpick();
        [$service, $itemService] = $this->serviceWithItemServiceMock();
        $itemService->method('accessibleChest')->willReturnCallback(
            static fn (User $user, int $itemId, ?Item $item = null): array => [$item ?? Item::query()->findOrFail($itemId), 'inventory'],
        );
        $user = User::query()->findOrFail(1);

        $started = $service->start($user, 100);
        $this->assertTrue($started->ok);
        $this->assertDatabaseHas('lockpicking_attempts', ['player_id' => 1, 'item_id' => 100]);

        $early = $service->complete($user, 100);
        $this->assertFalse($early->ok);
        $this->assertSame(425, $early->httpCode);

        $cancelled = $service->cancel($user, 100);
        $this->assertTrue($cancelled->ok);
        $this->assertDatabaseMissing('lockpicking_attempts', ['player_id' => 1]);
        $this->assertDatabaseHas('lockpicking_logs', ['player_id' => 1, 'result' => 'cancelled']);
    }

    public function test_player_cannot_use_lockpick_above_profession_level(): void
    {
        $this->seedLockedChestAndLockpick();
        DB::table('share_item_lockpick_configs')->where('share_item_id', 20)->update(['tier' => 2]);
        [$service, $itemService] = $this->serviceWithItemServiceMock();
        $itemService->method('accessibleChest')->willReturnCallback(
            static fn (User $user, int $itemId, ?Item $item = null): array => [$item ?? Item::query()->findOrFail($itemId), 'inventory'],
        );

        $result = $service->start(User::query()->findOrFail(1), 100, 20);

        $this->assertFalse($result->ok);
        $this->assertSame(422, $result->httpCode);
        $this->assertStringContainsString('50 уровня', $result->message);
        $this->assertDatabaseMissing('lockpicking_attempts', ['player_id' => 1]);
    }

    public function test_complete_cancels_attempt_if_player_dies_during_lockpicking(): void
    {
        Carbon::setTestNow('2026-09-15 12:00:00');
        $this->seedLockedChestAndLockpick();
        [$service, $itemService] = $this->serviceWithItemServiceMock();
        $itemService->method('accessibleChest')->willReturnCallback(
            static fn (User $user, int $itemId, ?Item $item = null): array => [$item ?? Item::query()->findOrFail($itemId), 'inventory'],
        );
        $user = User::query()->findOrFail(1);
        $this->assertTrue($service->start($user, 100)->ok);

        Carbon::setTestNow('2026-09-15 12:00:12');
        DB::table('players')->where('id', 1)->update(['hp_now' => 0]);
        $result = $service->complete(User::query()->findOrFail(1), 100);

        $this->assertFalse($result->ok);
        $this->assertSame(422, $result->httpCode);
        $this->assertSame('cancelled', $result->data['status']);
        $this->assertDatabaseMissing('lockpicking_attempts', ['player_id' => 1]);
    }

    public function test_failed_attempt_allows_retry_when_another_lockpick_remains(): void
    {
        Carbon::setTestNow('2026-09-15 12:00:00');
        $this->seedLockedChestAndLockpick();
        DB::table('backpacks')->where('item_id', 200)->update(['count' => 2]);
        DB::table('lockpicking_attempts')->insert([
            'player_id' => 1,
            'item_id' => 100,
            'share_item_id' => 10,
            'location_id' => null,
            'is_inventory' => true,
            'skill_snapshot' => 1,
            'chance_snapshot' => 0,
            'started_at' => now()->subSeconds(12),
            'completes_at' => now(),
            'expires_at' => now()->addSeconds(30),
        ]);
        [$service, $itemService] = $this->serviceWithItemServiceMock();
        $itemService->method('accessibleChest')->willReturnCallback(
            static fn (User $user, int $itemId, ?Item $item = null): array => [$item ?? Item::query()->findOrFail($itemId), 'inventory'],
        );

        $result = $service->complete(User::query()->findOrFail(1), 100);

        $this->assertTrue($result->ok);
        $this->assertSame('failure', $result->data['status']);
        $this->assertTrue($result->data['has_lockpick']);
        $this->assertDatabaseHas('backpacks', ['item_id' => 200, 'count' => 1]);
        $this->assertDatabaseMissing('lockpicking_attempts', ['player_id' => 1]);
    }

    public function test_trap_reports_actual_damage_and_applied_effect(): void
    {
        $sheet = new StatSheet;
        $sheet->hpMax = 100;
        $statService = $this->createMock(PlayerStatService::class);
        $statService->expects($this->once())
            ->method('resolve')
            ->willReturn($sheet);

        $effect = new Effect;
        $effect->forceFill([
            'id' => 19,
            'name' => 'Слабость',
            'description' => 'Снижает характеристики.',
        ]);
        $battleEffectService = $this->createMock(BattleEffectService::class);
        $battleEffectService->expects($this->once())
            ->method('applyEffectToPlayer')
            ->with(
                $effect,
                $this->isInstanceOf(Player::class),
                null,
                $this->isInstanceOf(AttackResultDTO::class),
                120,
            );

        $service = new LockpickingService(
            $this->createMock(ItemService::class),
            $this->createMock(PeacefulProfessionExperienceService::class),
            $battleEffectService,
            $statService,
        );
        $player = Player::query()->findOrFail(1);
        $method = new \ReflectionMethod(LockpickingService::class, 'triggerTrap');

        $result = $method->invoke($service, $player, $effect, 120, 5);

        $this->assertSame(5, $result['damage']);
        $this->assertSame(19, $result['effect']['id']);
        $this->assertSame('Слабость', $result['effect']['name']);
        $this->assertSame('Снижает характеристики.', $result['effect']['description']);
        $this->assertSame(120, $result['effect']['duration_seconds']);
        $this->assertSame(95, $player->fresh()->hp_now);
    }

    private function service(): LockpickingService
    {
        return $this->serviceWithItemServiceMock()[0];
    }

    /** @return array{LockpickingService, ItemService&MockObject} */
    private function serviceWithItemServiceMock(): array
    {
        $itemService = $this->createMock(ItemService::class);

        return [
            new LockpickingService(
                $itemService,
                $this->createMock(PeacefulProfessionExperienceService::class),
                $this->createMock(BattleEffectService::class),
                $this->createMock(PlayerStatService::class),
            ),
            $itemService,
        ];
    }

    private function seedPlayer(): void
    {
        DB::table('locations')->insert(['id' => 1]);
        DB::table('players')->insert(['id' => 1, 'user_id' => 1, 'hp_now' => 100]);
        DB::table('users')->insert([
            'id' => 1,
            'player_id' => 1,
            'location_id' => 1,
            'name' => 'Взломщик',
            'email' => 'lockpicker@test.local',
            'password' => 'x',
        ]);
    }

    private function seedLockedChestAndLockpick(): void
    {
        DB::table('skills')->insert(['id' => 1, 'name' => LockpickingService::SKILL_NAME, 'type' => 'peaceful']);
        DB::table('share_items')->insert([
            ['id' => 10, 'name' => 'Сундук', 'type' => 'chest', 'is_lockpick' => false],
            ['id' => 20, 'name' => 'Отмычка', 'type' => 'misc', 'is_lockpick' => true],
        ]);
        DB::table('share_item_lock_configs')->insert([
            'share_item_id' => 10,
            'lock_required_skill' => 1,
            'lock_duration_seconds' => 12,
            'experience_reward' => 3,
        ]);
        DB::table('share_item_lockpick_configs')->insert([
            'share_item_id' => 20,
            'tier' => 1,
            'speed_bonus_percent' => 0,
            'failure_preserve_chance_percent' => 0,
            'trap_avoid_chance_percent' => 0,
        ]);
        DB::table('items')->insert([
            ['id' => 100, 'share_item_id' => 10, 'is_open' => false],
            ['id' => 200, 'share_item_id' => 20, 'is_open' => false],
        ]);
        DB::table('backpacks')->insert([
            ['user_id' => 1, 'item_id' => 100, 'equipped' => false, 'count' => 1],
            ['user_id' => 1, 'item_id' => 200, 'equipped' => false, 'count' => 1],
        ]);
    }

    private function createTables(): void
    {
        Schema::create('locations', fn (Blueprint $table) => $table->id());
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('hp_now');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('location_id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('gathering_attempts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });
        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->integer('lvl')->default(1);
            $table->integer('exp')->default(0);
            $table->integer('exp_up')->default(100);
            $table->integer('exp_diff')->default(100);
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_lockpick')->default(false);
            $table->timestamps();
        });
        Schema::create('share_item_lock_configs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedSmallInteger('lock_required_skill');
            $table->unsignedSmallInteger('lock_duration_seconds');
            $table->unsignedSmallInteger('experience_reward')->nullable();
            $table->unsignedTinyInteger('minimum_success_chance_percent')->default(5);
            $table->unsignedTinyInteger('trap_chance_penalty_percent')->default(0);
            $table->unsignedBigInteger('trap_effect_id')->nullable();
            $table->unsignedSmallInteger('trap_effect_duration_seconds')->default(60);
            $table->unsignedTinyInteger('trap_damage_percent')->default(0);
            $table->timestamps();
        });
        Schema::create('share_item_lockpick_configs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedTinyInteger('tier');
            $table->unsignedTinyInteger('speed_bonus_percent')->default(0);
            $table->unsignedTinyInteger('failure_preserve_chance_percent')->default(0);
            $table->unsignedTinyInteger('trap_avoid_chance_percent')->default(0);
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->timestamps();
        });
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->integer('count')->default(1);
            $table->timestamps();
        });
        Schema::create('lockpicking_attempts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->unique();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('lockpick_share_item_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->boolean('is_inventory');
            $table->unsignedSmallInteger('skill_snapshot');
            $table->unsignedTinyInteger('lockpick_tier_snapshot')->default(1);
            $table->unsignedTinyInteger('speed_bonus_snapshot')->default(0);
            $table->unsignedTinyInteger('failure_preserve_chance_snapshot')->default(0);
            $table->unsignedTinyInteger('trap_avoid_chance_snapshot')->default(0);
            $table->decimal('chance_snapshot', 5, 2);
            $table->timestamp('started_at');
            $table->timestamp('completes_at');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::create('lockpicking_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('lockpick_share_item_id')->nullable();
            $table->string('source');
            $table->string('result');
            $table->unsignedSmallInteger('skill_level');
            $table->unsignedTinyInteger('lockpick_tier')->default(1);
            $table->decimal('success_chance', 5, 2);
            $table->boolean('trap_triggered');
            $table->boolean('lockpick_broken')->default(false);
            $table->boolean('trap_avoided')->default(false);
            $table->timestamps();
        });
    }
}
