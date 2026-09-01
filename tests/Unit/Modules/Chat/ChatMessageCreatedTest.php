<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Events\ChatMessageCreated;
use App\Modules\Chat\Domain\Events\ChatMessageExpired;
use App\Modules\Chat\Domain\Models\ChatMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChatMessageCreatedTest extends TestCase
{
    #[DataProvider('scopedChannels')]
    public function test_message_is_broadcast_only_to_its_relevant_scope(
        ChatChannel $channel,
        array $attributes,
        array $expectedChannels,
    ): void {
        $message = (new ChatMessage)->forceFill([
            'id' => 42,
            'channel' => $channel->value,
            'user_id' => 7,
            'target_user_id' => null,
            'map_id' => null,
            'clan_id' => null,
            'party_id' => null,
            ...$attributes,
        ]);

        $channels = array_map(
            static fn ($broadcastChannel): string => $broadcastChannel->name,
            (new ChatMessageCreated($message))->broadcastOn(),
        );

        $this->assertSame($expectedChannels, $channels);
    }

    public static function scopedChannels(): array
    {
        return [
            'main' => [ChatChannel::Main, [], ['private-chat.main']],
            'location' => [ChatChannel::Location, ['map_id' => 15], ['private-chat.location.15']],
            'clan' => [ChatChannel::Clan, ['clan_id' => 9], ['private-chat.clan.9']],
            'party' => [ChatChannel::Party, ['party_id' => 3], ['private-chat.party.3']],
            'private' => [
                ChatChannel::Private,
                ['target_user_id' => 11],
                ['private-App.Models.User.7', 'private-App.Models.User.11'],
            ],
            'personal system message' => [
                ChatChannel::Main,
                ['user_id' => null, 'target_user_id' => 11],
                ['private-App.Models.User.11'],
            ],
            'global system message' => [
                ChatChannel::System,
                ['user_id' => null],
                ['private-chat.system'],
            ],
        ];
    }

    public function test_private_expiration_only_hides_the_message_outside_private_history(): void
    {
        $message = (new ChatMessage)->forceFill([
            'id' => 42,
            'channel' => ChatChannel::Private->value,
            'user_id' => 7,
            'target_user_id' => 11,
        ]);

        $event = new ChatMessageExpired($message);

        $this->assertSame(
            ['private-App.Models.User.7', 'private-App.Models.User.11'],
            array_map(static fn ($channel): string => $channel->name, $event->broadcastOn()),
        );
        $this->assertTrue($event->broadcastWith()['preserve_in_private']);
    }
}
