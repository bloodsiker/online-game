<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Moderation;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Interface\Application\Services\PlayerEffectStateBroadcaster;
use App\Modules\Moderation\Application\Services\ChatMuteEffectService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

final class ChatMuteEffectServiceTest extends TestCase
{
    private PlayerEffectStateBroadcaster $effectStateBroadcaster;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('active_type')->nullable();
            $table->string('damage_scaling_type')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('chance')->default(0);
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stacks')->default(1);
            $table->integer('tick_interval')->default(1);
            $table->decimal('value_per_tick', 12, 6)->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_dispellable')->default(true);
            $table->timestamps();
        });
        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('source_player_id')->nullable();
            $table->unsignedBigInteger('source_magic_skill_id')->nullable();
            $table->timestamp('applied_at');
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('next_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->decimal('current_value', 12, 6)->nullable();
            $table->decimal('tick_remainder', 12, 6)->default(0);
            $table->timestamps();
        });

        Effect::query()->create([
            'name' => 'Проклятие молчания',
            'slug' => ChatMuteEffectService::EFFECT_SLUG,
            'type' => 'debuff',
        ]);

        $this->effectStateBroadcaster = Mockery::mock(PlayerEffectStateBroadcaster::class);
    }

    public function test_apply_creates_effect_and_reapplication_updates_its_expiration(): void
    {
        $player = new Player;
        $player->id = 5;
        $this->effectStateBroadcaster->shouldReceive('broadcast')->twice()->with($player);
        $service = new ChatMuteEffectService($this->effectStateBroadcaster);

        $service->apply($player, Carbon::parse('2026-09-18 13:00:00'));
        $service->apply($player, Carbon::parse('2026-09-18 14:00:00'));

        self::assertSame(1, PlayerActiveEffect::query()->count());
        self::assertSame(
            '2026-09-18 14:00:00',
            PlayerActiveEffect::query()->sole()->expires_at->format('Y-m-d H:i:s'),
        );
    }

    public function test_remove_deletes_chat_mute_effect(): void
    {
        $player = new Player;
        $player->id = 8;
        $this->effectStateBroadcaster->shouldReceive('broadcast')->twice()->with($player);
        $service = new ChatMuteEffectService($this->effectStateBroadcaster);
        $service->apply($player, Carbon::parse('2026-09-18 14:00:00'));

        $service->remove($player);

        self::assertDatabaseCount('player_active_effects', 0);
    }
}
