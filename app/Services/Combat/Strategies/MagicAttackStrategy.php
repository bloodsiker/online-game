<?php

namespace App\Services\Combat\Strategies;

use App\DTO\FightHitDTO;
use App\Models\Item\Item;
use App\Models\MagicSkill\MagicSkill;
use App\Models\Monster\Monster;
use App\Models\Player\PlayerEquipment;
use App\Services\Combat\HitCalculator;
use App\Services\Combat\FightHitInterface;

class MagicAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator       $hitCalc,
        private FightHitInterface   $player,     // это твой декорированный Player (с EquipmentDecorator + BuffDecorator)
        private Monster             $monster,
        private MagicSkill          $magicSkill,
    ) {}

    public function getHits(): array
    {
        if (!$this->magicSkill instanceof MagicSkill) {
            return [
                (new FightHitDTO())
                    ->setCantCast(true)
                    ->setMessage('Заклинание не изучено или отключено')
            ];
        }

        if (!$this->magicSkill->isAttackSkill()) {
            return [
                (new FightHitDTO())
                    ->setCantCast(true)
                    ->setMessage('Это не атакующее заклинание')
            ];
        }

        if ($this->player->mp_now < $this->magicSkill->mana_cost) {
            return [
                (new FightHitDTO())
                    ->setCantCast(true)
                    ->setMessage(sprintf('Недостаточно маны, требуется %s', $this->magicSkill->mana_cost))
            ];
        }

        $this->player->mp_now -= $this->magicSkill->mana_cost;

        // 1. Проверка уклонения — магию тоже можно увернуться (баланс настраивается в HitCalculator)
        $dodgeHit = $this->hitCalc->playerHit($this->player, $this->monster, 0, 0);
        if ($dodgeHit->isDodge()) {
            return [
                $dodgeHit
                    ->setWeaponName($this->magicSkill->name)
                    ->setWeapon(null)
            ];
        }

        // 2. Базовый урон от скилла
        $baseDamage = random_int($this->magicSkill->min_damage, $this->magicSkill->max_damage);

        // 3. Бонус от магического оружия (посох, жезл, книга и т.д.)
        // Предполагаем, что у Player есть методы:
        //   getMagicWeaponMinBonus() и getMagicWeaponMaxBonus()
        $weaponMinBonus = $this->player->getMagicWeaponMinBonus ?? 0;
        $weaponMaxBonus = $this->player->getMagicWeaponMaxBonus ?? 0;

//        $weaponBonus = $weaponMinBonus > 0 || $weaponMaxBonus > 0
//            ? random_int($weaponMinBonus, $weaponMaxBonus)
//            : 0;

        $totalMin = $baseDamage + $weaponMinBonus;
        $totalMax = $baseDamage + $weaponMaxBonus;

        // 4. Рассчитываем хит с итоговым диапазоном урона
        $hit = $this->hitCalc->playerHit($this->player, $this->monster, $totalMin, $totalMax);

        // Если вдруг хит критовал — всё ок, HitCalculator уже это посчитал
        // Если уклонения нет — продолжаем

        // 5. Применяем эффекты скилла с вероятностью
        foreach ($this->skill->effects as $effectData) {
            if (random_int(1, 100) <= $effectData->chance) {
                $appliedEffect = new AppliedEffect(
                    name:       $effectData->name,
                    type:       $effectData->type,
                    value:      $effectData->value,
                    duration:   $effectData->duration,
                    target:     $effectData->target, // 'enemy' или 'self'
                    icon:       $effectData->icon ?? null,
                );

                $hit->addAppliedEffect($appliedEffect);
            }
        }

        // 6. Устанавливаем красивые данные для лога и опыта
        return [
            $hit
                ->setWeaponName("заклинанием «{$this->magicSkill->name}»")
                ->setWeapon(null)                  // магия — не оружие
        ];
    }
}
