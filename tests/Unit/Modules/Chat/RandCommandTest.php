<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\UseCases\SendMessage;
use App\Modules\Chat\Domain\ChatMessageLifetime;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Tests\TestCase;

class RandCommandTest extends TestCase
{
    /** @var list<array<string, mixed>> */
    private array $created = [];

    private function useCase(): SendMessage
    {
        $repository = $this->createMock(ChatMessageRepositoryInterface::class);
        $repository->method('create')->willReturnCallback(function (array $data): ChatMessage {
            $this->created[] = $data;

            return new ChatMessage($data);
        });

        return new SendMessage($repository, $this->createMock(PartyRepositoryInterface::class), $this->createMock(CommunicationMuteService::class));
    }

    public function test_rand_command_sends_a_number_from_0_to_100_as_rand_type(): void
    {
        $sender = (new User)->forceFill(['id' => 7]);

        foreach (['/rand', '/RAND', '  /Rand  '] as $input) {
            $this->useCase()->execute($sender, $input, ChatChannel::Main);
        }

        $this->assertCount(3, $this->created);
        foreach ($this->created as $data) {
            $this->assertSame(ChatMessageType::Rand->value, $data['type']);
            $this->assertSame('main', $data['channel']);
            $this->assertSame(1, preg_match('/^🎲 (\d{1,3}) \(0–100\)$/u', $data['message'], $match));
            $this->assertGreaterThanOrEqual(0, (int) $match[1]);
            $this->assertLessThanOrEqual(100, (int) $match[1]);
        }
    }

    public function test_rand_with_extra_text_is_a_normal_message(): void
    {
        $this->useCase()->execute((new User)->forceFill(['id' => 7]), '/rand 1000', ChatChannel::Main);

        $this->assertSame(ChatMessageType::Message->value, $this->created[0]['type']);
        $this->assertSame('/rand 1000', $this->created[0]['message']);
    }

    public function test_rand_message_lives_ten_minutes(): void
    {
        $this->assertSame(10, ChatMessageLifetime::RAND_MINUTES);
    }
}
