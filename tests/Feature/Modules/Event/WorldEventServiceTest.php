<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Event;

use App\Modules\Event\Application\UseCases\GetActiveWorldEventWidget;
use App\Modules\Event\Application\UseCases\GetPlayerMapInfluences;
use App\Modules\Event\Application\UseCases\GetWorldEventCards;
use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Event\Domain\Events\WorldEventFinished;
use App\Modules\Event\Domain\Events\WorldEventStarted;
use App\Modules\Event\Domain\Events\WorldEventStateChanged;
use App\Modules\Event\Domain\Services\WorldEventCollectionService;
use App\Modules\Event\Domain\Services\WorldEventFavoriteService;
use App\Modules\Event\Domain\Services\WorldEventLifecycleService;
use App\Modules\Event\Domain\Services\WorldEventProgressService;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEvent;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;
use App\Modules\Influence\Domain\Events\MapInfluenceAwarded;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class WorldEventServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Event::fake([WorldEventStarted::class, WorldEventFinished::class, WorldEventStateChanged::class, MapInfluenceAwarded::class]);
        $this->createTables();
        $this->seedBaseData();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_event_starts_and_spawns_only_configured_amount(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $event = WorldEvent::query()->findOrFail(1);
        DB::table('world_event_locations')->where('world_event_id', $event->id)->delete();

        $run = app(WorldEventLifecycleService::class)->start($event->id, now());

        $this->assertNotNull($run);
        $this->assertSame(3, ItemOnLocation::query()->where('world_event_run_id', $run->id)->count());
        $this->assertEmpty(
            ItemOnLocation::query()
                ->where('world_event_run_id', $run->id)
                ->whereNotIn('location_id', [10, 11])
                ->pluck('id')
                ->all(),
        );
        $this->assertSame('2026-09-20 13:00:00', $run->ends_at->format('Y-m-d H:i:s'));
        $html = view('event::world-events', [
            'worldEventCards' => app(GetWorldEventCards::class)->execute(1, 'events'),
            'mode' => 'events',
        ])->render();
        $this->assertStringContainsString('id="event-run-'.$run->id.'"', $html);
        $this->assertStringContainsString('world-event-card__image user-rewards__item-pic', $html);
        $this->assertStringContainsString('user-rewards__item-image', $html);
        $this->assertStringContainsString('world-event-card__target-list', $html);
        $this->assertStringContainsString('data-id="20"', $html);
        $this->assertStringNotContainsString('<small>Осколок</small>', $html);
        $this->assertStringNotContainsString('world-event-card__stage', $html);
        Event::assertDispatched(WorldEventStarted::class, fn (WorldEventStarted $event): bool => $event->runId === $run->id
            && $event->title === 'Осколки Дартронга'
            && $event->mapName === 'Канализация'
            && $event->mapSlug === 'sewer');
    }

    public function test_collection_updates_global_personal_and_map_influence_and_enforces_limit(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $run = app(WorldEventLifecycleService::class)->start(1, now());
        $slots = ItemOnLocation::query()->where('world_event_run_id', $run->id)->limit(2)->get();
        $user = new User;
        $user->id = 1;
        $user->exists = true;
        $service = app(WorldEventCollectionService::class);

        $first = DB::transaction(fn () => $service->collect($user, $slots[0]));
        $second = DB::transaction(fn () => $service->collect($user, $slots[1]));

        $this->assertTrue($first->allowed);
        $this->assertSame(4, $first->influenceAwarded);
        $this->assertSame(4, $first->mapInfluence);
        $this->assertFalse($second->allowed);
        $this->assertSame(1, WorldEventRun::query()->findOrFail($run->id)->collected_count);
        $this->assertDatabaseHas('world_event_player_progress', ['user_id' => 1, 'collected_count' => 1, 'influence_earned' => 4]);
        $this->assertDatabaseHas('map_influences', ['user_id' => 1, 'map_id' => 1, 'influence' => 4]);
        $this->assertDatabaseMissing('map_influences', ['user_id' => 1, 'map_id' => 2]);
        $this->assertSame('2026-09-20 15:00:00', $slots[0]->item->fresh()->expires_at->format('Y-m-d H:i:s'));
        $this->assertSame(
            [['map' => 'Дартронг', 'influence' => 4]],
            app(GetPlayerMapInfluences::class)->execute(1)
                ->map(fn ($entry): array => ['map' => $entry->map->name, 'influence' => (int) $entry->influence])
                ->all(),
        );
        Event::assertDispatchedTimes(MapInfluenceAwarded::class, 1);
    }

    public function test_kill_event_spawns_monsters_and_credits_only_supplied_killer(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $run = app(WorldEventLifecycleService::class)->start(2, now());
        $user = new User;
        $user->id = 1;
        $user->exists = true;

        $result = app(WorldEventProgressService::class)->advance(
            user: $user,
            runId: $run->id,
            objectiveType: WorldEventObjectiveType::KILL,
            targetId: 30,
        );

        $this->assertSame(2, DB::table('monster_on_locations')->where('world_event_run_id', $run->id)->count());
        $this->assertTrue($result->allowed);
        $this->assertSame(1, WorldEventRun::query()->findOrFail($run->id)->collected_count);
        $this->assertDatabaseHas('map_influences', ['user_id' => 1, 'map_id' => 1, 'influence' => 3]);
    }

    public function test_stage_spawns_and_accepts_multiple_collection_targets(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        DB::table('share_items')->insert(['id' => 21, 'name' => 'Редкий осколок', 'type' => 'resource']);
        $stage = WorldEvent::query()->findOrFail(1)->stages()->firstOrFail();
        $stage->targets()->firstOrFail()->update(['max_active' => 1]);
        $stage->targets()->create([
            'position' => 2,
            'share_item_id' => 21,
            'spawn_weight' => 100,
            'max_active' => 2,
            'is_active' => true,
        ]);

        $run = app(WorldEventLifecycleService::class)->start(1, now());
        $spawnedByTarget = ItemOnLocation::query()
            ->join('items', 'items.id', '=', 'item_on_locations.item_id')
            ->where('item_on_locations.world_event_run_id', $run->id)
            ->selectRaw('items.share_item_id AS target_id, COUNT(*) AS target_count')
            ->groupBy('items.share_item_id')
            ->pluck('target_count', 'target_id')
            ->map(fn ($count): int => (int) $count)
            ->all();

        $this->assertSame([20 => 1, 21 => 2], $spawnedByTarget);

        $slot = ItemOnLocation::query()
            ->join('items', 'items.id', '=', 'item_on_locations.item_id')
            ->where('item_on_locations.world_event_run_id', $run->id)
            ->where('items.share_item_id', 21)
            ->select('item_on_locations.*')
            ->firstOrFail();
        $user = new User;
        $user->id = 1;
        $user->exists = true;

        $result = app(WorldEventCollectionService::class)->collect($user, $slot);

        $this->assertTrue($result->allowed);
        $this->assertSame(1, $result->playerProgress);
    }

    public function test_stage_spawns_and_accepts_multiple_monster_targets(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        DB::table('monsters')->insert(['id' => 31, 'name' => 'Редкий захватчик', 'hp' => 150]);
        $stage = WorldEvent::query()->findOrFail(2)->stages()->firstOrFail();
        $stage->targets()->firstOrFail()->update(['max_active' => 1]);
        $stage->targets()->create([
            'position' => 2,
            'monster_id' => 31,
            'spawn_weight' => 25,
            'max_active' => 1,
            'is_active' => true,
        ]);

        $run = app(WorldEventLifecycleService::class)->start(2, now());
        $this->assertSame(
            [30 => 1, 31 => 1],
            MonsterOnLocation::query()
                ->where('world_event_run_id', $run->id)
                ->selectRaw('monster_id AS target_id, COUNT(*) AS target_count')
                ->groupBy('monster_id')
                ->pluck('target_count', 'target_id')
                ->map(fn ($count): int => (int) $count)
                ->all(),
        );

        $user = new User;
        $user->id = 1;
        $user->exists = true;
        $result = app(WorldEventProgressService::class)->advance(
            user: $user,
            runId: $run->id,
            objectiveType: WorldEventObjectiveType::KILL,
            targetId: 31,
        );

        $this->assertTrue($result->allowed);
        $this->assertSame(1, $result->playerProgress);
    }

    public function test_active_event_is_hidden_from_future_and_returns_after_it_ends(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $event = WorldEvent::query()->findOrFail(1);
        $event->update(['next_start_at' => now(), 'repeat_interval_minutes' => 720]);
        app(WorldEventLifecycleService::class)->start($event->id, now());

        $this->assertTrue(app(GetWorldEventCards::class)->execute(1, 'events_future')->isEmpty());
        $this->assertNull($event->fresh()->next_start_at);

        Carbon::setTestNow('2026-09-20 13:00:01');
        app(WorldEventLifecycleService::class)->tick(now());

        $cards = app(GetWorldEventCards::class)->execute(1, 'events_future');
        $this->assertSame([$event->id], $cards->pluck('event.id')->all());
        $this->assertSame('2026-09-21 01:00:00', $event->fresh()->next_start_at->format('Y-m-d H:i:s'));

        $html = view('event::world-events', [
            'worldEventCards' => $cards,
            'mode' => 'events_future',
        ])->render();
        $this->assertStringContainsString('world-event-card__content--future', $html);
        $this->assertStringContainsString('world-event-card__future-summary', $html);
        $this->assertStringContainsString('<h3 class="world-event-card__title">', $html);
        $this->assertStringContainsString('Осколки Дартронга', $html);
        $this->assertStringContainsString('<b>Общая цель этапа:</b> 10.', $html);
        $this->assertMatchesRegularExpression('/world-event-card__future-summary.*<\/aside>\s*<div class="world-event-card__influence">/s', $html);
    }

    public function test_favorite_event_is_visible_without_rendering_finished_progress(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $run = app(WorldEventLifecycleService::class)->start(1, now());
        DB::table('world_event_player_progress')->insert([
            'world_event_run_id' => $run->id,
            'world_event_stage_id' => $run->current_stage_id,
            'user_id' => 1,
            'collected_count' => 1,
            'influence_earned' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $run->update([
            'status' => WorldEventRun::STATUS_COMPLETED,
            'finished_at' => now(),
        ]);
        app(WorldEventFavoriteService::class)->set(userId: 1, eventId: 1, favorite: true);

        $html = view('event::world-events', [
            'worldEventCards' => app(GetWorldEventCards::class)->execute(1, 'events_my'),
            'mode' => 'events_my',
        ])->render();

        $this->assertStringContainsString('Осколки Дартронга', $html);
        $this->assertStringContainsString('Удалить из избранного', $html);
        $this->assertStringNotContainsString('Общий прогресс', $html);
        $this->assertStringNotContainsString('Ваш прогресс', $html);
        $this->assertStringNotContainsString('Осталось собрать', $html);
    }

    public function test_player_can_add_and_remove_event_from_favorites(): void
    {
        $user = new User;
        $user->id = 1;
        $user->exists = true;
        $this->actingAs($user);
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->postJson(route('events.favorite', 1), ['favorite' => true])
            ->assertOk()
            ->assertJson(['ok' => true, 'favorite' => true]);

        $cards = app(GetWorldEventCards::class)->execute(1, 'events_my');
        $this->assertSame([1], $cards->pluck('event.id')->all());
        $this->assertTrue($cards->first()['isFavorite']);
        $this->assertDatabaseHas('world_event_favorites', ['user_id' => 1, 'world_event_id' => 1]);

        $this->postJson(route('events.favorite', 1), ['favorite' => false])
            ->assertOk()
            ->assertJson(['ok' => true, 'favorite' => false]);

        $this->assertTrue(app(GetWorldEventCards::class)->execute(1, 'events_my')->isEmpty());
        $this->assertDatabaseMissing('world_event_favorites', ['user_id' => 1, 'world_event_id' => 1]);
    }

    public function test_finishing_active_run_dispatches_finished_event_only_once(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $run = app(WorldEventLifecycleService::class)->start(1, now());
        $service = app(WorldEventLifecycleService::class);

        $service->finish($run->id, WorldEventRun::STATUS_EXPIRED, now());
        $service->finish($run->id, WorldEventRun::STATUS_EXPIRED, now());

        Event::assertDispatchedTimes(WorldEventFinished::class, 1);
        Event::assertDispatched(WorldEventFinished::class, fn (WorldEventFinished $event): bool => $event->runId === $run->id
            && $event->title === 'Осколки Дартронга');
    }

    public function test_reaching_global_goal_dispatches_finished_event(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $event = WorldEvent::query()->findOrFail(1);
        $event->update(['repeat_interval_minutes' => 720]);
        $event->stages()->firstOrFail()->update([
            'global_limit' => 1,
            'spawn_limit' => 1,
        ]);
        $run = app(WorldEventLifecycleService::class)->start(1, now());
        $slot = ItemOnLocation::query()->where('world_event_run_id', $run->id)->firstOrFail();
        $user = new User;
        $user->id = 1;
        $user->exists = true;

        $result = DB::transaction(fn () => app(WorldEventCollectionService::class)->collect($user, $slot));

        $this->assertTrue($result->allowed);
        $this->assertSame(WorldEventRun::STATUS_COMPLETED, $run->fresh()->status);
        $this->assertSame('2026-09-21 00:00:00', $event->fresh()->next_start_at->format('Y-m-d H:i:s'));
        Event::assertDispatchedTimes(WorldEventFinished::class, 1);
    }

    public function test_completing_stage_starts_next_stage_and_resets_personal_limit(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $event = WorldEvent::query()->findOrFail(1);
        $firstStage = $event->stages()->firstOrFail();
        $firstStage->update(['global_limit' => 1, 'player_limit' => 1, 'spawn_limit' => 1]);
        $secondStage = $event->stages()->create([
            'position' => 2,
            'title' => 'Сильные стражи',
            'objective_type' => 'kill',
            'monster_id' => 30,
            'global_limit' => 2,
            'player_limit' => 1,
            'spawn_limit' => 2,
            'respawn_seconds' => 60,
            'item_lifetime_minutes' => 120,
            'influence_per_item' => 5,
            'is_active' => true,
        ]);
        $secondStage->targets()->create([
            'position' => 1,
            'monster_id' => 30,
            'spawn_weight' => 100,
            'is_active' => true,
        ]);

        $run = app(WorldEventLifecycleService::class)->start($event->id, now());
        $slot = ItemOnLocation::query()->where('world_event_run_id', $run->id)->firstOrFail();
        $user = new User;
        $user->id = 1;
        $user->exists = true;

        $firstResult = DB::transaction(fn () => app(WorldEventCollectionService::class)->collect($user, $slot));

        $this->assertTrue($firstResult->allowed);
        $this->assertTrue($firstResult->stageAdvanced);
        $this->assertSame('Сильные стражи', $firstResult->nextStageTitle);
        $this->assertSame($secondStage->id, $run->fresh()->current_stage_id);
        $this->assertSame(0, $run->fresh()->collected_count);
        $this->assertSame(WorldEventRun::STATUS_ACTIVE, $run->fresh()->status);
        $this->assertSame(2, DB::table('monster_on_locations')->where('world_event_run_id', $run->id)->where('active', true)->count());
        Event::assertDispatchedTimes(WorldEventStateChanged::class, 2);

        $monster = MonsterOnLocation::query()
            ->where('world_event_run_id', $run->id)
            ->firstOrFail();
        $secondResult = DB::transaction(fn () => app(WorldEventProgressService::class)->advance(
            user: $user,
            runId: $run->id,
            objectiveType: WorldEventObjectiveType::KILL,
            targetId: (int) $monster->monster_id,
        ));

        $this->assertTrue($secondResult->allowed);
        $this->assertSame(1, $secondResult->playerProgress);
        $this->assertDatabaseCount('world_event_player_progress', 2);

        $html = view('event::world-events', [
            'worldEventCards' => app(GetWorldEventCards::class)->execute(1, 'events'),
            'mode' => 'events',
        ])->render();
        $this->assertStringContainsString('<p class="world-event-card__stage"><b>Этап 2</b></p>', $html);
        $this->assertStringNotContainsString('Этап 2 из 2', $html);
    }

    public function test_widget_contains_only_active_events_and_tracks_global_progress(): void
    {
        Carbon::setTestNow('2026-09-20 12:00:00');
        $run = app(WorldEventLifecycleService::class)->start(1, now());

        $this->assertSame([
            [
                'run_id' => $run->id,
                'title' => 'Осколки Дартронга',
                'stage_title' => 'Сбор осколков',
                'stage_position' => 1,
                'objective_type' => 'collect',
                'progress_label' => 'Собрано ресурсов',
                'collected_count' => 0,
                'global_limit' => 10,
                'remaining_seconds' => 3600,
            ],
        ], app(GetActiveWorldEventWidget::class)->execute());

        $run->update(['collected_count' => 4]);
        $this->assertSame(4, app(GetActiveWorldEventWidget::class)->execute()[0]['collected_count']);

        app(WorldEventLifecycleService::class)->finish($run->id, WorldEventRun::STATUS_EXPIRED, now());
        $this->assertSame([], app(GetActiveWorldEventWidget::class)->execute());
        Event::assertDispatchedTimes(WorldEventStateChanged::class, 3);
    }

    private function createTables(): void
    {
        Schema::create('users', fn (Blueprint $table) => $table->id());
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->string('name');
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('resource');
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->unsignedInteger('hp');
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('count')->default(1);
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('upgrade_lvl')->default(0);
            $table->unsignedInteger('upgrade_pity')->default(0);
            $table->unsignedInteger('upgrade_fail_streak')->default(0);
            $table->unsignedInteger('socket_count')->default(0);
            $table->unsignedInteger('rune_slot_count')->default(0);
            $table->integer('additional_attack')->default(0);
            $table->unsignedInteger('count_use')->default(0);
            $table->boolean('is_open')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('world_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('objective_type')->default('collect');
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('influence_map_id')->nullable();
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('monster_id')->nullable();
            $table->timestamp('next_start_at')->nullable();
            $table->unsignedInteger('repeat_interval_minutes')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('global_limit');
            $table->unsignedInteger('player_limit');
            $table->unsignedInteger('spawn_limit');
            $table->unsignedInteger('respawn_seconds');
            $table->unsignedInteger('item_lifetime_minutes');
            $table->unsignedInteger('influence_per_item');
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('world_event_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_id');
            $table->unsignedSmallInteger('position');
            $table->string('title');
            $table->string('objective_type');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('monster_id')->nullable();
            $table->unsignedInteger('global_limit');
            $table->unsignedInteger('player_limit');
            $table->unsignedInteger('spawn_limit');
            $table->unsignedInteger('respawn_seconds');
            $table->unsignedInteger('item_lifetime_minutes');
            $table->unsignedInteger('influence_per_item');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('world_event_stage_targets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_stage_id');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('monster_id')->nullable();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('spawn_weight')->default(100);
            $table->unsignedInteger('max_active')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('world_event_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_id');
            $table->unsignedBigInteger('location_id');
        });
        Schema::create('world_event_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_id');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->string('status');
            $table->unsignedInteger('collected_count')->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('next_spawn_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        Schema::create('world_event_player_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_run_id');
            $table->unsignedBigInteger('world_event_stage_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('collected_count')->default(0);
            $table->unsignedBigInteger('influence_earned')->default(0);
            $table->timestamps();
        });
        Schema::create('world_event_favorites', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('world_event_id');
            $table->timestamps();
            $table->unique(['user_id', 'world_event_id']);
        });
        Schema::create('map_influences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('influence')->default(0);
            $table->timestamps();
        });
        Schema::create('map_influence_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('map_id');
            $table->bigInteger('amount');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('map_influence_levels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedSmallInteger('level');
            $table->string('name');
            $table->unsignedBigInteger('required_influence');
            $table->unsignedBigInteger('influence_medal_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('item_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('world_event_run_id')->nullable();
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->integer('count')->default(1);
            $table->string('interaction_type')->default('pickup');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('world_event_run_id')->nullable();
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->unsignedInteger('hp_now');
            $table->unsignedInteger('hp_max');
            $table->boolean('active')->default(true);
            $table->boolean('is_drop_money')->default(false);
            $table->unsignedInteger('current_phase')->default(1);
            $table->unsignedInteger('aggression')->nullable();
            $table->timestamps();
        });
    }

    private function seedBaseData(): void
    {
        DB::table('users')->insert(['id' => 1]);
        DB::table('maps')->insert([
            ['id' => 1, 'parent_id' => null, 'name' => 'Дартронг', 'slug' => 'dartrong'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Канализация', 'slug' => 'sewer'],
        ]);
        DB::table('locations')->insert([
            ['id' => 10, 'map_id' => 2, 'name' => 'Старый коллектор'],
            ['id' => 11, 'map_id' => 2, 'name' => 'Затопленный тоннель'],
        ]);
        DB::table('share_items')->insert(['id' => 20, 'name' => 'Осколок', 'type' => 'resource']);
        DB::table('monsters')->insert(['id' => 30, 'name' => 'Событийный монстр', 'hp' => 100]);
        DB::table('world_events')->insert([
            'id' => 1,
            'title' => 'Осколки Дартронга',
            'objective_type' => 'collect',
            'map_id' => 2,
            'influence_map_id' => 1,
            'share_item_id' => 20,
            'duration_minutes' => 60,
            'global_limit' => 10,
            'player_limit' => 1,
            'spawn_limit' => 3,
            'respawn_seconds' => 60,
            'item_lifetime_minutes' => 120,
            'influence_per_item' => 4,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('world_event_stages')->insert([
            'id' => 1,
            'world_event_id' => 1,
            'position' => 1,
            'title' => 'Сбор осколков',
            'objective_type' => 'collect',
            'share_item_id' => 20,
            'global_limit' => 10,
            'player_limit' => 1,
            'spawn_limit' => 3,
            'respawn_seconds' => 60,
            'item_lifetime_minutes' => 120,
            'influence_per_item' => 4,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('world_event_stage_targets')->insert([
            'world_event_stage_id' => 1,
            'share_item_id' => 20,
            'position' => 1,
            'spawn_weight' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('world_event_locations')->insert([
            ['world_event_id' => 1, 'location_id' => 10],
            ['world_event_id' => 1, 'location_id' => 11],
        ]);
        DB::table('world_events')->insert([
            'id' => 2,
            'title' => 'Нашествие',
            'objective_type' => 'kill',
            'map_id' => 1,
            'influence_map_id' => 1,
            'monster_id' => 30,
            'duration_minutes' => 60,
            'global_limit' => 10,
            'player_limit' => 5,
            'spawn_limit' => 2,
            'respawn_seconds' => 60,
            'item_lifetime_minutes' => 120,
            'influence_per_item' => 3,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('world_event_stages')->insert([
            'id' => 2,
            'world_event_id' => 2,
            'position' => 1,
            'title' => 'Уничтожение захватчиков',
            'objective_type' => 'kill',
            'monster_id' => 30,
            'global_limit' => 10,
            'player_limit' => 5,
            'spawn_limit' => 2,
            'respawn_seconds' => 60,
            'item_lifetime_minutes' => 120,
            'influence_per_item' => 3,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('world_event_stage_targets')->insert([
            'world_event_stage_id' => 2,
            'monster_id' => 30,
            'position' => 1,
            'spawn_weight' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('world_event_locations')->insert(['world_event_id' => 2, 'location_id' => 10]);
    }
}
