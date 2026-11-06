<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;

final class QuestTestDoubles
{
    public static function bind(): void
    {
        Event::fake();
        $experience = Mockery::mock(ExperienceService::class);
        $experience->shouldReceive('calculateGain')->andReturnUsing(fn ($player, $amount) => $amount);
        app()->instance(ExperienceService::class, $experience);
        $reputation = Mockery::mock(ReputationService::class);
        $reputation->shouldReceive('syncReputationRating')->andReturn(0);
        app()->instance(ReputationService::class, $reputation);
        app()->instance(ChatService::class, new class extends ChatService
        {
            public function __construct() {}

            public function sendSystemToUser(User $user, string|LocalizedChatMessage $message): ChatMessage
            {
                DB::table('quest_test_notifications')->insert(['user_id' => $user->id]);

                return new ChatMessage;
            }

            public function sendQuestToUser(User $user, string|LocalizedChatMessage $message): ChatMessage
            {
                return $this->sendSystemToUser($user, $message);
            }
        });
    }
}
