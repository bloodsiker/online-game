<?php

namespace App\Services\Combat\Boss;

use App\DTO\AttackResultDTO;
use App\Models\Battle\Battle;

readonly class BossShieldService
{
    /**
     * Перевірка чи є активний щит
     */
    public function hasActiveShield(Battle $battle): bool
    {
        $metadata = $battle->boss_metadata ?? [];
        $shield = $metadata['shield'] ?? null;

        if (!$shield) {
            return false;
        }

        // Перевіряємо чи не закінчився час дії
        if ($shield['expires_at_turn'] < $battle->rounds) {
            $this->removeShield($battle);
            return false;
        }

        // Перевіряємо чи щит не зламано
        if ($shield['hp'] <= 0) {
            $this->removeShield($battle);
            return false;
        }

        return true;
    }

    /**
     * Нанести урон по щиту
     * Повертає залишок урону який пройшов крізь щит
     */
    public function damageShield(Battle $battle, int $damage, AttackResultDTO $result): int
    {
        if (!$this->hasActiveShield($battle)) {
            return $damage;
        }

        $metadata = $battle->boss_metadata ?? [];
        $shield = $metadata['shield'];

        $shieldHp = $shield['hp'];
        $damageToShield = min($damage, $shieldHp);
        $remainingDamage = max(0, $damage - $shieldHp);

        // Зменшуємо HP щита
        $shield['hp'] -= $damageToShield;
        $metadata['shield'] = $shield;
        $battle->boss_metadata = $metadata;
        $battle->save();

        // Логуємо урон по щиту
        if ($shield['hp'] > 0) {
            $result->log(sprintf(
                '<p><b class="color-shield">🛡️ Щит поглинає %d урону! (Залишилось: %d/%d)</b></p>',
                $damageToShield,
                $shield['hp'],
                $shield['max_hp']
            ));
        } else {
            $result->log(sprintf(
                '<p><b class="color-shield-break">💥 Щит зламано! Поглинуто %d урону, пробито %d урону!</b></p>',
                $damageToShield,
                $remainingDamage
            ));
            $this->removeShield($battle);
        }

        return $remainingDamage;
    }

    /**
     * Видалити щит
     */
    public function removeShield(Battle $battle): void
    {
        $metadata = $battle->boss_metadata ?? [];
        unset($metadata['shield']);
        $battle->boss_metadata = $metadata;
        $battle->save();
    }

    /**
     * Оновити тривалість щита (зменшити на початку ходу)
     */
    public function updateShieldDuration(Battle $battle, AttackResultDTO $result): void
    {
        if (!$this->hasActiveShield($battle)) {
            return;
        }

        $metadata = $battle->boss_metadata ?? [];
        $shield = $metadata['shield'];

        // Перевіряємо чи закінчився щит
        if ($shield['expires_at_turn'] <= $battle->rounds) {
            $result->log(sprintf(
                '<p><b class="color-info">🛡️ Щит босса рассеялся!</b></p>'
            ));
            $this->removeShield($battle);
        }
    }

    /**
     * Отримати інформацію про щит
     */
    public function getShieldInfo(Battle $battle): ?array
    {
        if (!$this->hasActiveShield($battle)) {
            return null;
        }

        $metadata = $battle->boss_metadata ?? [];
        return $metadata['shield'] ?? null;
    }

    /**
     * Отримати відсоток HP щита
     */
    public function getShieldHpPercent(Battle $battle): ?float
    {
        $shield = $this->getShieldInfo($battle);

        if (!$shield) {
            return null;
        }

        return ($shield['hp'] / $shield['max_hp']) * 100;
    }
}
