<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Events\PlayerInjured;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerInjury;
use Illuminate\Support\Collection;

final readonly class PlayerInjuryService
{
    public function __construct(private RandomizerInterface $random) {}

    public function inflictAfterDeath(Player $player): ?PlayerInjury
    {
        if (! config('injuries.enabled', true)
            || ! $this->random->chance((float) config('injuries.chance_percent', 30))) {
            return null;
        }

        $rolledType = $this->rollInjuryType();
        if ($rolledType === null) {
            return null;
        }

        $bodyPart = $rolledType->body_part;
        $now = now();

        $injury = PlayerInjury::query()
            ->where('player_id', $player->id)
            ->where('body_part', $bodyPart->value)
            ->lockForUpdate()
            ->first();
        $injury?->loadMissing('injuryType');
        $hasActiveInjury = $injury?->expires_at->isAfter($now) === true;

        $injuryType = $hasActiveInjury && $injury->severity->value > $rolledType->severity->value
            ? ($injury->injuryType ?? $rolledType)
            : $rolledType;
        $newExpiry = $now->copy()->addSeconds($injuryType->duration_seconds);

        if ($injury === null) {
            $injury = new PlayerInjury([
                'player_id' => $player->id,
                'injury_type_id' => $injuryType->id,
                'body_part' => $bodyPart,
            ]);
        } elseif ($hasActiveInjury && $injury->expires_at->isAfter($newExpiry)) {
            $newExpiry = $injury->expires_at;
        }

        $injury->injury_type_id = $injuryType->id;
        $injury->severity = $injuryType->severity;
        $injury->applied_at = $now;
        $injury->expires_at = $newExpiry;
        $injury->save();
        $injury->setRelation('injuryType', $injuryType);

        $this->unequipBlockedItem($player, $bodyPart);
        $player->unsetRelation('playerEquip');
        $player->unsetRelation('injuries');
        PlayerInjured::dispatch($player, $injury);

        return $injury;
    }

    /** @return Collection<string, PlayerInjury> */
    public function activeByEquipmentColumn(Player $player): Collection
    {
        if (! config('injuries.enabled', true)) {
            return collect();
        }

        return PlayerInjury::query()
            ->with('injuryType')
            ->where('player_id', $player->id)
            ->active()
            ->get()
            ->keyBy(static fn (PlayerInjury $injury): string => $injury->body_part->equipmentColumn());
    }

    public function pruneExpired(?Player $player = null): int
    {
        if (! config('injuries.enabled', true)) {
            return 0;
        }

        return PlayerInjury::query()
            ->when($player !== null, fn ($query) => $query->where('player_id', $player->id))
            ->where('expires_at', '<=', now())
            ->delete();
    }

    private function rollInjuryType(): ?InjuryType
    {
        $types = InjuryType::query()->active()->orderBy('id')->get();
        $totalWeight = (int) $types->sum('drop_weight');
        if ($totalWeight <= 0) {
            return null;
        }

        $roll = $this->random->between(1, $totalWeight);
        foreach ($types as $type) {
            $roll -= $type->drop_weight;
            if ($roll <= 0) {
                return $type;
            }
        }

        return null;
    }

    private function unequipBlockedItem(Player $player, InjuryBodyPart $bodyPart): void
    {
        $equipment = PlayerEquipment::query()
            ->where('player_id', $player->id)
            ->lockForUpdate()
            ->first();

        if ($equipment === null) {
            return;
        }

        $column = $bodyPart->equipmentColumn();
        $itemId = $equipment->getAttribute($column);

        if ($bodyPart->isHand()) {
            [$column, $itemId] = $this->resolveTwoHandedItem($equipment, $column, $itemId);
        }

        if ($itemId === null) {
            return;
        }

        $equipment->setAttribute($column, null);
        if ($bodyPart->isHand()) {
            foreach (['hand_left', 'hand_right'] as $handColumn) {
                if ((int) $equipment->getAttribute($handColumn) === (int) $itemId) {
                    $equipment->setAttribute($handColumn, null);
                }
            }
        }
        $equipment->save();

        Backpack::query()
            ->where('user_id', $player->user_id)
            ->where('item_id', $itemId)
            ->update(['equipped' => false]);
    }

    /** @return array{string, int|null} */
    private function resolveTwoHandedItem(PlayerEquipment $equipment, string $column, mixed $itemId): array
    {
        if ($itemId !== null) {
            return [$column, (int) $itemId];
        }

        $otherColumn = $column === 'hand_left' ? 'hand_right' : 'hand_left';
        $otherItemId = $equipment->getAttribute($otherColumn);
        if ($otherItemId === null) {
            return [$column, null];
        }

        $otherItem = Item::query()->with('itemInfo')->find($otherItemId);
        if (! $otherItem?->itemInfo?->is_two_hand) {
            return [$column, null];
        }

        return [$otherColumn, (int) $otherItemId];
    }
}
