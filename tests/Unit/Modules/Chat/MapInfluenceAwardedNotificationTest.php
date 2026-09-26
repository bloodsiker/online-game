<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Listeners\SendMapInfluenceAwardedMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Influence\Domain\Events\MapInfluenceAwarded;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Mockery;
use Tests\TestCase;

final class MapInfluenceAwardedNotificationTest extends TestCase
{
    public function test_listener_sends_personal_message_in_reputation_style(): void
    {
        $user = new User;
        $user->id = 17;

        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendQuestToUser')
            ->once()
            ->with($user, 'Ваше влияние на карте <b>«Дартронг»</b> увеличено на <b>+5</b>.')
            ->andReturn(new ChatMessage);

        (new SendMapInfluenceAwardedMessage($chatService))->handle(
            new MapInfluenceAwarded($user, 'Дартронг', 5, 25),
        );
    }
}
