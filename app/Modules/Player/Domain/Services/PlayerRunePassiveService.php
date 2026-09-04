<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Structure\Blacksmith\Domain\Enums\RunePassiveType;

/**
 * Пассивки вплавленных рун читаются live из экипировки игрока (см.
 * PlayerStatService::equipmentStatModifiers()) — единственный источник
 * правды это сама руна в слоте предмета, без синхронизации в отдельную
 * таблицу (в отличие от клановых скиллов, которые копируются в
 * player_magic_skills при вступлении в клан/прокачке).
 */
class PlayerRunePassiveService
{
    public function __construct(
        private readonly PlayerEquipmentLoader $equipmentLoader,
    ) {}

    /**
     * @return list<array{type: RunePassiveType, value: int, runeName: string, runeShareItemId: int, itemName: string, handSide: ?string}>
     */
    public function resolve(Player $player): array
    {
        $equip = $this->equipmentLoader->load($player);

        if (! $equip) {
            return [];
        }

        // handSide != null только для рук — пассивка привязана к конкретному
        // оружию (см. AttackService::applyOffensiveRunePassives()), у брони и
        // прочей экипировки такой привязки нет — они срабатывают от любого удара.
        $handSlots = ['left' => $equip->handLeft, 'right' => $equip->handRight];

        $otherSlots = [
            $equip->helmetSlot, $equip->shoulderSlot, $equip->forearmSlot,
            $equip->armorSlot, $equip->leggingSlot, $equip->chainArmorSlot,
            $equip->cloakSlot, $equip->shoesSlot, $equip->glovesSlot,
            $equip->beltFirstSlot, $equip->beltSecondSlot,
            $equip->bagFirstSlot, $equip->bagSecondSlot,
        ];

        $passives = [];

        foreach ($handSlots as $handSide => $item) {
            if (! $item) {
                continue;
            }

            foreach ($item->runes as $rune) {
                if ($rune->passive_skill === null) {
                    continue;
                }

                $passives[] = [
                    'type' => RunePassiveType::from($rune->passive_skill['type']),
                    'value' => (int) $rune->passive_skill['value'],
                    'runeName' => $rune->runeInfo->name,
                    'runeShareItemId' => $rune->runeInfo->id,
                    'itemName' => $item->itemInfo->name,
                    'handSide' => $handSide,
                ];
            }
        }

        foreach (array_filter($otherSlots) as $item) {
            foreach ($item->runes as $rune) {
                if ($rune->passive_skill === null) {
                    continue;
                }

                $passives[] = [
                    'type' => RunePassiveType::from($rune->passive_skill['type']),
                    'value' => (int) $rune->passive_skill['value'],
                    'runeName' => $rune->runeInfo->name,
                    'runeShareItemId' => $rune->runeInfo->id,
                    'itemName' => $item->itemInfo->name,
                    'handSide' => null,
                ];
            }
        }

        return $passives;
    }

    /**
     * Суммарное значение всех пассивок данного типа (несколько рун с одной
     * и той же пассивкой складываются, как и обычные статы предметов).
     *
     * $requiredHandSide задаётся только для пассивок, привязанных к
     * конкретному удару оружия (Вампиризм/Оглушение/Двойной удар/Цепная
     * атака) — тогда учитываются пассивки этой руки плюс пассивки с
     * предметов вне рук (handSide === null). Для остального (Ярость, Щит,
     * Отражение) параметр не передаётся — они не привязаны к удару.
     *
     * $requiredHandSide === null также приходит от хитов не из руки (магия,
     * кулак — FightHitDTO::getHandSide()) — в этом случае фильтрации нет
     * специально: руна оружия в руке всё равно должна сработать, даже если
     * урон в этот раз нанесён заклинанием, а не самим оружием.
     *
     * @param  list<array{type: RunePassiveType, value: int, runeName: string, runeShareItemId: int, itemName: string, handSide: ?string}>  $passives
     */
    public function totalValue(array $passives, RunePassiveType $type, ?string $requiredHandSide = null): int
    {
        return array_sum(array_map(
            fn (array $p) => $p['value'],
            array_filter($passives, function (array $p) use ($type, $requiredHandSide) {
                if ($p['type'] !== $type) {
                    return false;
                }

                return $requiredHandSide === null || $p['handSide'] === null || $p['handSide'] === $requiredHandSide;
            }),
        ));
    }
}
