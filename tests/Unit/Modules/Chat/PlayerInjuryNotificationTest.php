<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Listeners\SendPlayerInjuryMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Enums\InjurySeverity;
use App\Modules\Player\Domain\Events\PlayerInjured;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerInjury;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

final class PlayerInjuryNotificationTest extends TestCase
{
    public function test_listener_sends_personal_information_message_about_injury(): void
    {
        $user = new User;
        $user->id = 17;

        $player = new Player;
        $player->setRelation('user', $user);

        $injuryType = new InjuryType([
            'name' => 'Перелом левой руки',
            'body_part' => InjuryBodyPart::LEFT_HAND,
            'severity' => InjurySeverity::SEVERE,
            'duration_seconds' => 1800,
        ]);
        $injury = new PlayerInjury([
            'body_part' => InjuryBodyPart::LEFT_HAND,
            'severity' => InjurySeverity::SEVERE,
            'applied_at' => Carbon::parse('2026-09-07 12:00:00'),
            'expires_at' => Carbon::parse('2026-09-07 12:30:00'),
        ]);
        $injury->setRelation('injuryType', $injuryType);

        $expected = '<b style="color:#ba0000">Получена травма: Перелом левой руки на 30 мин.</b>';
        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendSystemToUser')
            ->once()
            ->with($user, $expected)
            ->andReturn(new ChatMessage);

        (new SendPlayerInjuryMessage($chatService))->handle(new PlayerInjured($player, $injury));
    }
}
