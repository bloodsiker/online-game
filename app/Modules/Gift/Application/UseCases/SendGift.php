<?php

declare(strict_types=1);

namespace App\Modules\Gift\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\UseCases\SendSystemMessage;
use App\Modules\Gift\Application\DTOs\SendGiftResult;
use App\Modules\Gift\Domain\Contracts\PlayerGiftRepository;
use App\Modules\Item\Domain\Enums\ItemActionType;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Дарит один предмет типа «gift» из рюкзака отправителя игроку по нику.
 * Предмет списывается, у получателя появляется запись в player_gifts (навсегда) и личное сообщение в чате.
 */
final class SendGift
{
    public function __construct(
        private readonly PlayerGiftRepository $gifts,
        private readonly ItemActionLogger $itemActionLogger,
        private readonly SendSystemMessage $systemMessage,
    ) {}

    public function execute(User $sender, int $backpackId, string $recipientName, ?string $message, bool $isAnonymous): SendGiftResult
    {
        $recipient = User::query()->without('player')->where('name', trim($recipientName))->first();
        if ($recipient === null) {
            return SendGiftResult::fail(__('gift.recipient_not_found', ['name' => trim($recipientName)]));
        }
        if ((int) $recipient->id === (int) $sender->id) {
            return SendGiftResult::fail(__('gift.self'));
        }
        $message = $this->cleanMessage($message);

        $result = DB::transaction(function () use ($sender, $recipient, $backpackId, $message, $isAnonymous): SendGiftResult|array {
            $backpack = Backpack::query()->whereKey($backpackId)->where('user_id', $sender->id)->lockForUpdate()->first();
            if ($backpack === null || (int) $backpack->count < 1) {
                return SendGiftResult::fail(__('gift.unavailable'));
            }
            if ($backpack->isEquipped()) {
                return SendGiftResult::fail(__('gift.unavailable'));
            }
            $shareItem = $backpack->item?->itemInfo;
            if ($shareItem === null || $shareItem->type !== ShareItemType::GIFT || ! $shareItem->is_active) {
                return SendGiftResult::fail(__('gift.not_a_gift'));
            }

            $remaining = $this->takeOne($backpack);
            $gift = $this->gifts->create(
                recipientId: (int) $recipient->id,
                senderId: (int) $sender->id,
                shareItemId: (int) $shareItem->id,
                message: $message,
                isAnonymous: $isAnonymous,
            );
            $this->itemActionLogger->log($sender, $shareItem, 0, ItemActionType::GIVE, 1, null, $recipient);

            return ['gift' => $gift, 'shareItem' => $shareItem, 'remaining' => $remaining];
        });

        if ($result instanceof SendGiftResult) {
            return $result;
        }

        $this->notifyRecipient($recipient, $sender, $result['shareItem'], $isAnonymous);

        return new SendGiftResult(
            ok: true,
            message: __('gift.sent', ['gift' => $result['shareItem']->name, 'name' => $recipient->name]),
            remainingCount: $result['remaining'],
        );
    }

    /** Списывает одну штуку; пустую строку рюкзака и осиротевший экземпляр предмета удаляет. */
    private function takeOne(Backpack $backpack): int
    {
        if ((int) $backpack->count > 1) {
            $backpack->decrement('count');

            return (int) $backpack->count;
        }
        $itemId = (int) $backpack->item_id;
        $backpack->delete();
        if (! Backpack::query()->where('item_id', $itemId)->exists()) {
            Item::query()->whereKey($itemId)->delete();
        }

        return 0;
    }

    private function cleanMessage(?string $message): ?string
    {
        $message = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $message)) ?? '');

        return $message === '' ? null : mb_substr($message, 0, 150);
    }

    private function notifyRecipient(User $recipient, User $sender, mixed $shareItem, bool $isAnonymous): void
    {
        $gift = LocalizedChatMessage::contentName($shareItem);
        $this->systemMessage->toUser($recipient, $isAnonymous
            ? new LocalizedChatMessage('chat.gift_received_anonymous', ['gift' => $gift])
            : new LocalizedChatMessage('chat.gift_received', ['gift' => $gift, 'user_id' => (int) $sender->id]));
    }
}
