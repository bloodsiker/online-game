<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\PlayerTrade\Infrastructure\Persistence\Models\PlayerTradeSession;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Domain\Services\ArtifactUpgradeRules;
use App\Modules\Structure\Blacksmith\Infrastructure\Persistence\ArtifactUpgradeConfig;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SharpenArtifact
{
    public function execute(User $user, int $structureId, int $itemId, int $expectedLevel): int
    {
        return DB::transaction(function () use ($user, $structureId, $itemId, $expectedLevel): int {
            $owner = User::query()->setEagerLoads([])->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $structure = Structure::query()->setEagerLoads([])->findOrFail($structureId);
            abort_unless($structure->type === Structure::TYPE_BLACKSMITH, 404);
            $this->ensure((int) $structure->location_id === (int) $owner->location_id, 'Для заточки нужно находиться в кузне.');
            $trading = PlayerTradeSession::query()
                ->whereIn('status', [PlayerTradeSession::PENDING, PlayerTradeSession::ACTIVE])
                ->where('expires_at', '>', now())
                ->where(fn ($query) => $query->where('initiator_user_id', $owner->id)->orWhere('target_user_id', $owner->id))
                ->exists();
            $this->ensure(! $trading, 'Завершите торговлю перед заточкой.');

            $slots = Backpack::query()->where('user_id', $owner->id)->orderBy('id')->lockForUpdate()->get();
            $instances = Item::query()->with(['itemInfo' => fn ($query) => $query->setEagerLoads([])])
                ->whereIn('id', $slots->pluck('item_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($slots as $slot) {
                $slot->setRelation('item', $instances->get($slot->item_id));
            }
            $target = $slots->firstWhere('item_id', $itemId);
            $this->ensure($target !== null && ! $target->equipped && (int) $target->count === 1, 'Предмет должен находиться в рюкзаке.');
            $item = $target->item;
            $this->ensure($item !== null && $item->itemInfo->type === ShareItemType::ARTIFACT, 'Выберите артефакт.');
            $this->ensure($item->expires_at === null || $item->expires_at->isFuture(), 'Срок действия предмета истёк.');
            $this->ensure((int) $item->upgrade_lvl === $expectedLevel, 'Уровень заточки изменился. Обновите страницу.');
            $this->ensure($expectedLevel >= 0 && $expectedLevel < ArtifactUpgradeRules::MAX_LEVEL, 'Достигнут максимальный уровень заточки.');
            $config = ArtifactUpgradeConfig::query()->with('materials')->where('share_item_id', $item->share_item_id)->lockForUpdate()->first();
            $this->ensure($config !== null && $config->is_enabled, 'Для этого артефакта заточка отключена.');
            $this->ensure($owner->money >= $config->gold_cost, 'Недостаточно монет.');

            $available = $slots->filter(fn (Backpack $slot): bool => ! $slot->equipped
                && (int) $slot->id !== (int) $target->id && $slot->item !== null
                && ($slot->item->expires_at === null || $slot->item->expires_at->isFuture())
                && (int) $slot->item->upgrade_lvl === 0);
            $donor = $available->first(fn (Backpack $slot): bool => (int) $slot->item->share_item_id === (int) $item->share_item_id && $slot->count > 0);
            $this->ensure($donor !== null, 'Нужна копия этого артефакта с заточкой +0.');
            $remaining = $available->mapWithKeys(fn (Backpack $slot): array => [$slot->id => (int) $slot->count])->all();
            $remaining[$donor->id]--;
            $consumption = [$donor->id => 1];
            foreach ($config->materials as $material) {
                $needed = (int) $material->pivot->count;
                foreach ($available as $slot) {
                    if ((int) $slot->item->share_item_id !== (int) $material->id) {
                        continue;
                    }
                    $take = min($needed, $remaining[$slot->id]);
                    $remaining[$slot->id] -= $take;
                    $consumption[$slot->id] = ($consumption[$slot->id] ?? 0) + $take;
                    $needed -= $take;
                }
                $this->ensure($needed === 0, 'Не хватает материалов для заточки.');
            }
            foreach ($consumption as $slotId => $quantity) {
                if ($quantity === 0) {
                    continue;
                }
                $slot = $slots->firstWhere('id', $slotId);
                if ($quantity === (int) $slot->count) {
                    $slot->delete();
                    $slot->item->delete();
                } else {
                    $slot->decrement('count', $quantity);
                }
            }
            if ($config->gold_cost > 0) {
                $owner->decrement('money', $config->gold_cost);
            }
            $item->upgrade_lvl = $expectedLevel + 1;
            $item->save();

            return (int) $item->upgrade_lvl;
        });
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['artifact' => __($message)]);
        }
    }
}
