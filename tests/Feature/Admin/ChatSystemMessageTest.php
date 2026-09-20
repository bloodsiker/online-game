<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use Mockery;
use Tests\TestCase;

final class ChatSystemMessageTest extends TestCase
{
    public function test_admin_chat_page_contains_sender_and_help_catalog(): void
    {
        $this->withoutMiddleware(AdminMiddleware::class);

        $this->get(route('admin.chat.index'))
            ->assertOk()
            ->assertSee('Отправить системное сообщение')
            ->assertSee('Тип сообщения')
            ->assertSee('Глобальное системное')
            ->assertSee('30 минут');
    }

    public function test_admin_can_send_formatted_global_system_message(): void
    {
        $this->withoutMiddleware(AdminMiddleware::class);

        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendSystem')
            ->once()
            ->with('<b>Внимание</b><br>Событие', null, null, ChatMessageType::QuestItem)
            ->andReturn(new ChatMessage);
        $this->app->instance(ChatService::class, $chatService);

        $this->post(route('admin.chat.send'), [
            'message' => '<b>Внимание</b><br>Событие',
            'type' => ChatMessageType::QuestItem->value,
        ])->assertRedirect(route('admin.chat.index'))
            ->assertSessionHas('success');
    }

    public function test_system_message_must_have_at_least_two_characters(): void
    {
        $this->withoutMiddleware(AdminMiddleware::class);

        $this->post(route('admin.chat.send'), [
            'message' => 'Я',
            'type' => ChatMessageType::System->value,
        ])
            ->assertSessionHasErrors('message');
    }

    public function test_admin_cannot_send_internal_message_type(): void
    {
        $this->withoutMiddleware(AdminMiddleware::class);

        $this->post(route('admin.chat.send'), [
            'message' => 'Тестовое сообщение',
            'type' => ChatMessageType::PartyInvite->value,
        ])->assertSessionHasErrors('type');
    }
}
