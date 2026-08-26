<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\UseCases\SendMessage;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\Party\Infrastructure\Persistence\Models\Party;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class PartyChatChannelTest extends TestCase
{
    #[Test]
    public function member_message_is_bound_to_the_current_party(): void
    {
        $chatRepository = $this->createMock(ChatMessageRepositoryInterface::class);
        $partyRepository = $this->createMock(PartyRepositoryInterface::class);
        $user = $this->user(7);
        $party = new Party;
        $party->id = 42;

        $partyRepository->expects($this->once())
            ->method('findActiveByUser')
            ->with(7)
            ->willReturn($party);
        $chatRepository->expects($this->once())
            ->method('create')
            ->with($this->callback(fn (array $data) => $data['channel'] === 'party'
                && $data['party_id'] === 42
                && $data['message'] === 'Собираемся у ворот'))
            ->willReturn(new ChatMessage);

        (new SendMessage($chatRepository, $partyRepository))
            ->execute($user, 'Собираемся у ворот', ChatChannel::Party);
    }

    #[Test]
    public function player_without_a_party_cannot_write_to_party_channel(): void
    {
        $chatRepository = $this->createMock(ChatMessageRepositoryInterface::class);
        $partyRepository = $this->createMock(PartyRepositoryInterface::class);
        $user = $this->user(7);

        $partyRepository->expects($this->once())
            ->method('findActiveByUser')
            ->with(7)
            ->willReturn(null);
        $chatRepository->expects($this->never())->method('create');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Канал группы доступен только её участникам.');

        (new SendMessage($chatRepository, $partyRepository))
            ->execute($user, 'Меня здесь быть не должно', ChatChannel::Party);
    }

    private function user(int $id): User
    {
        $user = new User;
        $user->id = $id;

        return $user;
    }
}
