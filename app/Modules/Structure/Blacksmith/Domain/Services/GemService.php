<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Enums\ShareItemType;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\Item\ItemGem;
use App\Models\User;

class GemService
{
    public const MAX_SOCKETS = 3;

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
            'item_id'       => $item->id,
            'socket_index'  => $socketIndex,
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
     * Open a new socket on an item (requires socket kit from backpack).
     */
    public function openSocket(User $user, Item $item, Backpack $kitSlot): array
    {
        if ($item->socket_count >= self::MAX_SOCKETS) {
            return ['success' => false, 'message' => 'Достигнуто максимальное количество сокетов.'];
        }

        if ($kitSlot->count > 1) {
            $kitSlot->count -= 1;
            $kitSlot->save();
        } else {
            $kitSlot->delete();
            $kitSlot->item->delete();
        }

        $item->socket_count += 1;
        $item->save();

        return [
            'success' => true,
            'message' => sprintf('Сокет открыт. Теперь у предмета %d сокет(а/ов).', $item->socket_count),
        ];
    }
}