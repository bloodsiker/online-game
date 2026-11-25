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
            $this->assertSame(1, preg_match('/^Кинул случайное число (\d{1,3}) из 100$/u', $data['message'], $match));
            $this->assertGreaterThanOrEqual(0, (int) $match[1]);
            $this->assertLessThanOrEqual(100, (int) $match[1]);
            $this->assertSame('chat.rand_result_max', $data['translation_key']);
            $this->assertSame((int) $match[1], $data['translation_params']['number']);
            $this->assertSame(100, $data['translation_params']['max']);
        }
    }

    public function test_rand_with_a_number_rolls_from_zero_to_that_number(): void
    {
        $sender = (new User)->forceFill(['id' => 7]);

        for ($i = 0; $i < 30; $i++) {
            $this->useCase()->execute($sender, '/rand 300', ChatChannel::Main);
        }

        foreach ($this->created as $data) {
            $this->assertSame(ChatMessageType::Rand->value, $data['type']);
            $this->assertSame(300, $data['translation_params']['max']);
            $this->assertGreaterThanOrEqual(0, $data['translation_params']['number']);
            $this->assertLessThanOrEqual(300, $data['translation_params']['number']);
            $this->assertSame(
                'Кинул случайное число '.$data['translation_params']['number'].' из 300',
                $data['message'],
            );
        }
    }

    public function test_rand_number_is_capped_and_zero_or_garbage_is_a_normal_message(): void
    {
        $sender = (new User)->forceFill(['id' => 7]);
        $this->useCase()->execute($sender, '/rand 999999999', ChatChannel::Main);
        $this->assertSame(1_000_000, $this->created[0]['translation_params']['max']);

        foreach (['/rand 0', '/rand abc', '/rand 5 6', '/rand -5'] as $input) {
            $this->useCase()->execute($sender, $input, ChatChannel::Main);
        }
        foreach (array_slice($this->created, 1) as $data) {
            $this->assertSame(ChatMessageType::Message->value, $data['type']);
        }
    }

    public function test_rand_message_lives_ten_minutes(): void
    {
        $this->assertSame(10, ChatMessageLifetime::RAND_MINUTES);
    }
}
