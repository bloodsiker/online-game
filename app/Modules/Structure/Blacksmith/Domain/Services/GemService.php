<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemGem;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GemService
{
    public const MAX_SOCKETS = 4;

    /**
     * Insert gem into item socket.
     * Consumes gem from backpack.
     * Returns ['success' => bool, 'message' => string]
     */
    public function insertGem(User $user, Backpack $itemSlot, Backpack $gemSlot, int $socketIndex): array
    {
        $item = $itemSlot->item;
        $gemShareItem = $gemSlot->item->itemInfo;

        if ($gemShareItem->type !== ShareItemType::GEM) {
            return ['success' => false, 'message' => 'Выбранный предмет не является камнем.'];
        }

        if ($socketIndex < 0 || $socketIndex >= $item->socket_count) {
            return ['success' => false, 'message' => 'Неверный номер сокета.'];
        }

        $alreadyFilled = ItemGem::where('item_id', $item->id)
            ->where('socket_index', $socketIndex)
            ->exists();

        if ($alreadyFilled) {
            return ['success' => false, 'message' => 'Сокет уже занят. Сначала извлеките камень.'];
        }

        if ($gemSlot->count > 1) {
            $gemSlot->count -= 1;
            $gemSlot->save();
        } else {
            $gemSlot->delete();
            $gemSlot->item->delete();
        }

        ItemGem::create([
            'item_id' => $item->id,
            'socket_index' => $socketIndex,
            'share_item_id' => $gemShareItem->id,
        ]);

        return [
            'success' => true,
            'message' => sprintf('Камень «%s» вставлен в сокет %d.', $gemShareItem->name, $socketIndex + 1),
        ];
    }

    /**
     * Remove gem from socket and return it to backpack.
     * Returns ['success' => bool, 'message' => string]
     */
    public function removeGem(User $user, Item $item, int $socketIndex): array
    {
        $itemGem = ItemGem::where('item_id', $item->id)
            ->where('socket_index', $socketIndex)
            ->first();

        if (! $itemGem instanceof ItemGem) {
            return ['success' => false, 'message' => 'Сокет пуст.'];
        }

        $gemShareItemId = $itemGem->share_item_id;

        $existingSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('items.share_item_id', $gemShareItemId)
            ->first();

        if ($existingSlot instanceof Backpack) {
            $existingSlot->count += 1;
            $existingSlot->save();
        } else {
            $newItem = new Item;
            $newItem->share_item_id = $gemShareItemId;
            $newItem->save();

            $user->backpack()->attach($newItem->id, ['equipped' => 0, 'count' => 1]);
        }

        $gemName = $itemGem->gemInfo->name;
        $itemGem->delete();

        return [
            'success' => true,
            'message' => sprintf('Камень «%s» извлечён и возвращён в рюкзак.', $gemName),
        ];
    }

    /**
     * Устанавливает оправу на предмет. Количество открытых сокетов —
     * случайное число в диапазоне редкости оправы (см.
     * MountRarityConfig::socketRange(), редкость берётся из стандартного
     * share_items.rarity), плюс стоимость установки в монетах.
     *
     * Если на предмете уже есть сокеты, новую оправу можно поставить поверх
     * ради апгрейда — но это риск: оправа и монеты расходуются в любом
     * случае, а socket_count меняется только если выпавшее число сокетов
     * не меньше текущего (иначе результат — впустую потраченные ресурсы,
     * существующие сокеты и камни в них не трогаются).
     */
    public function openSocket(User $user, Item $item, Backpack $mountSlot): array
    {
        $mountInfo = $mountSlot->item->itemInfo;

        if ($mountInfo->type !== ShareItemType::MOUNT) {
            return ['success' => false, 'message' => 'Выбранный предмет не является оправой.'];
        }

        $rarity = $mountInfo->rarity;
        $cost = MountRarityConfig::openCost($rarity);

        if ($user->money < $cost) {
            return ['success' => false, 'message' => sprintf('Недостаточно монет. Нужно: %d', $cost)];
        }

        [$min, $max] = MountRarityConfig::socketRange($rarity);
        $rolledCount = min(random_int($min, $max), self::MAX_SOCKETS);

        $this->consumeFromBackpack($mountSlot);
        $user->money -= $cost;
        $user->save();

        if ($rolledCount < $item->socket_count) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Оправа «%s» не улучшила предмет (выпало %d сокет(ов), уже открыто %d). Оправа и монеты потрачены впустую.',
                    $mountInfo->name,
                    $rolledCount,
                    $item->socket_count
                ),
            ];
        }

        $item->socket_count = $rolledCount;
        $item->save();

        return [
            'success' => true,
            'message' => sprintf(
                'Оправа «%s» установлена. Открыто сокетов: %d. Потрачено: %d монет.',
                $mountInfo->name,
                $rolledCount,
                $cost
            ),
        ];
    }

    private function consumeFromBackpack(Backpack $slot): void
    {
        if ($slot->count > 1) {
            $slot->count -= 1;
            $slot->save();
        } else {
            $slot->delete();
            $slot->item->delete();
        }
    }
}
