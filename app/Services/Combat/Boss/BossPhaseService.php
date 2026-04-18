<?php

namespace App\Services\Combat\Boss;

use App\DTO\AttackResultDTO;
use App\Models\Battle\Battle;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;

class BossPhaseService
{
    /**
     * Перевірка і активація фази боса
     */
    public function checkAndTriggerPhase(
        MonsterOnLocation $locationMonster,
        Battle $battle,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;

        if (! $monster->isBoss()) {
            return;
        }

        $currentPhase = $this->getCurrentPhase($battle);
        $hpPercent = ($locationMonster->hp_now / $monster->hp) * 100;

        // Шукаємо наступну фазу
        $nextPhase = $monster->phases()
            ->where('phase_number', '>', $currentPhase)
            ->where('hp_threshold', '>=', $hpPercent)
            ->orderBy('phase_number')
            ->first();

        if (! $nextPhase) {
            return;
        }

        // Активуємо нову фазу
        $this->activatePhase($locationMonster, $battle, $nextPhase, $result);
    }

    /**
     * Активація фази з збереженням всіх даних в boss_metadata
     */
    private function activatePhase(
        MonsterOnLocation $locationMonster,
        Battle $battle,
        $phase,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;
        $metadata = $battle->boss_metadata ?? [];

        // Зберігаємо інформацію про зміну фази
        $metadata['current_phase'] = $phase->phase_number;
        $metadata['phase_history'][] = [
            'phase' => $phase->phase_number,
            'round' => $battle->rounds,
            'hp_percent' => round(($locationMonster->hp_now / $monster->hp) * 100, 2),
            'timestamp' => now()->toDateTimeString(),
        ];

        // Застосовуємо модифікатори характеристик
        if ($phase->stats_modifiers) {
            $statsModified = $metadata['stats_modified'] ?? [];

            foreach ($phase->stats_modifiers as $stat => $modifier) {
                $baseValue = $monster->{$stat} ?? 0;
                $modifiedValue = $baseValue + ($baseValue * $modifier / 100);

                // Зберігаємо модифіковане значення
                $statsModified[$stat] = [
                    'base' => $baseValue,
                    'modifier_percent' => $modifier,
                    'current' => $modifiedValue,
                    'phase' => $phase->phase_number,
                ];
            }

            $metadata['stats_modified'] = $statsModified;
        }

        // Зберігаємо активні скіли для поточної фази
        //        $metadata['active_skills'] = $this->calculateActiveSkills($monster, $phase->phase_number);
        $metadata['active_skills'] = [];

        // Зберігаємо оновлені метадані
        $battle->boss_metadata = $metadata;
        $battle->save();

        // Також оновлюємо current_phase в MonsterOnLocation для швидкого доступу
        $locationMonster->current_phase = $phase->phase_number;
        $locationMonster->save();

        // Логування переходу фази
        $result->log(sprintf(
            '<p><b class="color-phase">⚠️ %s переходить у фазу %d!</b></p>',
            $monster->name,
            $phase->phase_number
        ));

        if ($phase->description) {
            $result->log(sprintf(
                '<p><i class="color-info">%s</i></p>',
                $phase->description
            ));
        }

        // Інформація про зміни характеристик
        if ($phase->stats_modifiers) {
            foreach ($phase->stats_modifiers as $stat => $modifier) {
                $sign = $modifier > 0 ? '+' : '';
                $statName = $this->getStatName($stat);

                $result->log(sprintf(
                    '<p class="color-stat-change">%s: %s%d%%</p>',
                    $statName,
                    $sign,
                    $modifier
                ));
            }
        }

        // Додаємо нові скіли
        if ($phase->new_skills) {
            $result->log(sprintf(
                '<p class="color-warning">⚔️ %s отримує нові здібності: %s</p>',
                $monster->name,
                implode(', ', $phase->new_skills)
            ));
        }

        // Видалені скіли
        if ($phase->removed_skills) {
            $result->log(sprintf(
                '<p class="color-info">🚫 %s втрачає здібності: %s</p>',
                $monster->name,
                implode(', ', $phase->removed_skills)
            ));
        }
    }

    /**
     * Розрахунок активних скілів з урахуванням всіх фаз до поточної
     */
    private function calculateActiveSkills($monster, int $currentPhase): array
    {
        // Базові скіли монстра
        $baseSkills = $monster->skills->pluck('skill_code')->toArray();

        // Додані і видалені скіли з усіх попередніх фаз до поточної
        $phases = $monster->phases()
            ->where('phase_number', '<=', $currentPhase)
            ->orderBy('phase_number')
            ->get();

        $skills = $baseSkills;

        foreach ($phases as $phase) {
            if ($phase->new_skills) {
                $skills = array_merge($skills, $phase->new_skills);
            }
            if ($phase->removed_skills) {
                $skills = array_diff($skills, $phase->removed_skills);
            }
        }

        return array_values(array_unique($skills));
    }

    /**
     * Отримання поточної фази з метаданих
     */
    public function getCurrentPhase(Battle $battle): int
    {
        $metadata = $battle->boss_metadata ?? [];

        return $metadata['current_phase'] ?? 1;
    }

    /**
     * Отримання модифікованого значення характеристики
     */
    public function getModifiedStat(Battle $battle, string $stat): ?float
    {
        $metadata = $battle->boss_metadata ?? [];
        $statsModified = $metadata['stats_modified'] ?? [];

        return $statsModified[$stat]['current'] ?? null;
    }

    /**
     * Отримання всіх модифікаторів поточної фази
     */
    public function getPhaseModifiers(Battle $battle): array
    {
        $metadata = $battle->boss_metadata ?? [];
        $statsModified = $metadata['stats_modified'] ?? [];

        $modifiers = [];
        foreach ($statsModified as $stat => $data) {
            $modifiers[$stat] = $data['modifier_percent'];
        }

        return $modifiers;
    }

    /**
     * Отримання доступних скілів для поточної фази
     */
    public function getPhaseSkills(Battle $battle): array
    {
        $metadata = $battle->boss_metadata ?? [];

        return $metadata['active_skills'] ?? [];
    }

    /**
     * Отримання базового значення характеристики
     */
    public function getBaseStat(Battle $battle, string $stat): ?float
    {
        $metadata = $battle->boss_metadata ?? [];
        $statsModified = $metadata['stats_modified'] ?? [];

        return $statsModified[$stat]['base'] ?? null;
    }

    /**
     * Отримання історії змін фаз
     */
    public function getPhaseHistory(Battle $battle): array
    {
        $metadata = $battle->boss_metadata ?? [];

        return $metadata['phase_history'] ?? [];
    }

    /**
     * Перевірка чи активний скіл у поточній фазі
     */
    public function isSkillActive(Battle $battle, string $skillCode): bool
    {
        $activeSkills = $this->getPhaseSkills($battle);

        return in_array($skillCode, $activeSkills);
    }

    /**
     * Отримання читабельної назви характеристики
     */
    private function getStatName(string $stat): string
    {
        return match ($stat) {
            'attack' => 'Атака',
            'defence', 'defense' => 'Защита',
            'hp' => 'Здоров\'я',
            'min_dmg', 'min_damage' => 'Мин. урон',
            'max_dmg', 'max_damage' => 'Макс. урон',
            'speed' => 'Скорость',
            'critical_chance' => 'Шанс крита',
            'dodge_chance' => 'Шанс уклонення',
            default => ucfirst($stat),
        };
    }

    /**
     * Скидання модифікаторів (наприклад, при перезапуску бою)
     */
    public function resetPhaseModifiers(Battle $battle): void
    {
        $metadata = $battle->boss_metadata ?? [];

        unset($metadata['stats_modified']);
        unset($metadata['current_phase']);
        unset($metadata['active_skills']);
        unset($metadata['phase_history']);

        $metadata['current_phase'] = 1;

        $battle->boss_metadata = $metadata;
        $battle->save();
    }
}
