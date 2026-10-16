<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Listeners\SendPlayerInjuryMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
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
            'name' => 'Лёгкая травма ног',
            'body_part' => InjuryBodyPart::LEGS,
            'severity' => InjurySeverity::LIGHT,
            'duration_seconds' => 1800,
        ]);
        $injury = new PlayerInjury([
            'body_part' => InjuryBodyPart::LEGS,
            'severity' => InjurySeverity::LIGHT,
            'applied_at' => Carbon::parse('2026-09-07 12:00:00'),
            'expires_at' => Carbon::parse('2026-09-07 12:30:00'),
        ]);
        $injury->setRelation('injuryType', $injuryType);

        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendSystemToUser')
            ->once()
            ->withArgs(function (User $actualUser, LocalizedChatMessage $message) use ($user): bool {
                self::assertSame($user, $actualUser);
                self::assertSame('Лёгкая травма ног', $message->params['name']['translations']['ru']);
                self::assertSame('Легка травма ніг', $message->params['name']['translations']['uk']);
                self::assertSame(
                    '<b style="color:#ba0000">Отримано травму: Легка травма ніг на 30 хв.</b>',
                    app(ChatMessageTranslator::class)->render($message, 'uk'),
                );

                return true;
            })
            ->andReturn(new ChatMessage);

        (new SendPlayerInjuryMessage($chatService))->handle(new PlayerInjured($player, $injury));
    }
}
