<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Moderation;

use App\Modules\Moderation\Application\Services\ChatMuteEffectService;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Moderation\Domain\Events\ChatMuteImposed;
use App\Modules\Moderation\Domain\Events\CommunicationMuteChanged;
use App\Modules\Moderation\Domain\Exceptions\UserMutedException;
use App\Modules\Moderation\Domain\Models\UserCommunicationMute;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class CommunicationMuteServiceTest extends TestCase
{
    private ChatMuteEffectService $chatMuteEffectService;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Event::fake([ChatMuteImposed::class, CommunicationMuteChanged::class]);
        $this->chatMuteEffectService = Mockery::mock(ChatMuteEffectService::class);
        $this->chatMuteEffectService->shouldReceive('apply')->andReturn(new PlayerActiveEffect)->byDefault();
        $this->chatMuteEffectService->shouldReceive('remove')->byDefault();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->string('name');
            $table->boolean('is_admin')->default(false);
            $table->unsignedInteger('warehouse_count')->default(50);
            $table->unsignedInteger('bag_count')->default(25);
            $table->unsignedInteger('slot_count')->default(3);
            $table->timestamps();
        });

        Schema::create('user_communication_mutes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('imposed_by_user_id')->nullable();
            $table->string('scope', 20);
            $table->string('reason', 500)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('revoked_by_user_id')->nullable();
            $table->timestamps();
        });

        Carbon::setTestNow('2026-09-18 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_scopes_are_independent_and_repeated_mute_replaces_active_record(): void
    {
        $target = User::query()->create(['name' => 'Игрок']);
        $target->setRelation('player', (new Player)->forceFill(['id' => 1]));
        $moderator = User::query()->create(['name' => 'Модератор']);
        $service = new CommunicationMuteService($this->chatMuteEffectService);

        $firstForumMute = $service->mute($target, $moderator, CommunicationScope::Forum, 60, 'Флуд');
        $secondForumMute = $service->mute($target, $moderator, CommunicationScope::Forum, 120, 'Повторный флуд');
        $chatMute = $service->mute($target, $moderator, CommunicationScope::Chat, 30);

        self::assertNotNull($firstForumMute->fresh()->revoked_at);
        self::assertTrue($secondForumMute->isActive());
        self::assertTrue($chatMute->isActive());
        self::assertSame(2, UserCommunicationMute::query()->active()->count());
        self::assertSame($secondForumMute->id, $service->activeMute($target, CommunicationScope::Forum)?->id);
        Event::assertDispatched(
            ChatMuteImposed::class,
            fn (ChatMuteImposed $event): bool => $event->userId === $target->id
                && $event->durationMinutes === 30,
        );
    }

    public function test_expired_mute_does_not_block_and_active_mute_reports_expiration(): void
    {
        $target = User::query()->create(['name' => 'Игрок']);
        $target->setRelation('player', (new Player)->forceFill(['id' => 1]));
        $moderator = User::query()->create(['name' => 'Модератор']);
        $service = new CommunicationMuteService($this->chatMuteEffectService);

        $service->mute($target, $moderator, CommunicationScope::Chat, 30, 'Нарушение правил');

        try {
            $service->throwIfMuted($target, CommunicationScope::Chat);
            self::fail('Активное молчание должно блокировать отправку сообщения.');
        } catch (UserMutedException $exception) {
            self::assertStringContainsString('до 18.09.2026 12:30', $exception->getMessage());
            self::assertStringContainsString('Нарушение правил', $exception->getMessage());
        }

        Carbon::setTestNow('2026-09-18 12:31:00');

        $service->throwIfMuted($target, CommunicationScope::Chat);
        self::assertNull($service->activeMute($target, CommunicationScope::Chat));
    }
}
