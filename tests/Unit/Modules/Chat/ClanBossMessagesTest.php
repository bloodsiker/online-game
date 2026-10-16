<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Listeners\SendClanBossDefeatedMessage;
use App\Modules\Chat\Application\Listeners\SendClanBossRunExpiredMessage;
use App\Modules\Chat\Application\Listeners\SendClanBossRunStartedMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Application\UseCases\SendSystemMessage;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Dungeon\Domain\Events\ClanBossDefeated;
use App\Modules\Dungeon\Domain\Events\ClanBossRunExpired;
use App\Modules\Dungeon\Domain\Events\ClanBossRunStarted;
use Mockery;
use Tests\TestCase;

final class ClanBossMessagesTest extends TestCase
{
    public function test_run_started_message_is_localized_and_sent_to_clan_channel(): void
    {
        $this->expectClanNotice(
            17,
            'Клан розпочав похід у підземелля <b>«Лігво»</b>! Розпочався бій із босом <b>«Вартовий»</b>.',
        );

        (new SendClanBossRunStartedMessage(app(ChatService::class)))->handle(new ClanBossRunStarted(
            clanId: 17,
            dungeonNameTranslations: ['ru' => 'Логово', 'uk' => 'Лігво'],
            bossNameTranslations: ['ru' => 'Страж', 'uk' => 'Вартовий'],
        ));

        $this->assertClanMessagePayload('chat.clan_boss_run_started', [
            'dungeon' => ['translations' => ['ru' => 'Логово', 'uk' => 'Лігво']],
            'boss' => ['translations' => ['ru' => 'Страж', 'uk' => 'Вартовий']],
        ]);
    }

    public function test_boss_defeated_message_is_localized_and_sent_to_clan_channel(): void
    {
        $this->expectClanNotice(17, 'Клан здолав боса <b>«Вартовий»</b>!');

        (new SendClanBossDefeatedMessage(app(ChatService::class)))->handle(new ClanBossDefeated(
            clanId: 17,
            bossNameTranslations: ['ru' => 'Страж', 'uk' => 'Вартовий'],
        ));

        $this->assertClanMessagePayload('chat.clan_boss_defeated', [
            'boss' => ['translations' => ['ru' => 'Страж', 'uk' => 'Вартовий']],
        ]);
    }

    public function test_expired_run_message_is_localized_and_sent_to_clan_channel(): void
    {
        $this->expectClanNotice(17, 'Похід до кланового підземелля <b>«Лігво»</b> завершився за часом.');

        (new SendClanBossRunExpiredMessage(app(ChatService::class)))->handle(new ClanBossRunExpired(
            clanId: 17,
            dungeonNameTranslations: ['ru' => 'Логово', 'uk' => 'Лігво'],
        ));

        $this->assertClanMessagePayload('chat.clan_boss_run_expired', [
            'dungeon' => ['translations' => ['ru' => 'Логово', 'uk' => 'Лігво']],
        ]);
    }

    private function expectClanNotice(int $clanId, string $ukMessage): void
    {
        $chat = Mockery::mock(ChatService::class);
        $chat->shouldReceive('sendClanNotice')
            ->once()
            ->with($clanId, Mockery::on(fn ($message): bool => $message instanceof LocalizedChatMessage
                && app(ChatMessageTranslator::class)->render($message, 'uk') === ':speak: '.$ukMessage))
            ->andReturn(new ChatMessage);
        app()->instance(ChatService::class, $chat);
    }

    private function assertClanMessagePayload(string $key, array $params): void
    {
        $repository = Mockery::mock(ChatMessageRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => null,
                'channel' => ChatChannel::Clan->value,
                'clan_id' => 17,
                'message' => '',
                'translation_key' => $key,
                'translation_params' => $params,
                'type' => ChatMessageType::ClanBoss->value,
            ])
            ->andReturn(new ChatMessage);

        (new SendSystemMessage($repository))->toClan(17, new LocalizedChatMessage($key, $params));
    }
}
