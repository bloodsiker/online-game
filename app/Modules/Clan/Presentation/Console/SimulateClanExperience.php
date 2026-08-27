<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Console;

use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Player\Domain\Services\ExperienceCurve;
use Illuminate\Console\Command;

/**
 * Симуляция накопления опыта клана (clans.experience) — калибровка таблицы
 * порогов clan-level 1..N под реальный темп фарма, а не расчёт на бумаге.
 *
 * Клан получает 1%/3%/5% от опыта игрока за килл в зависимости от разницы
 * «уровень моба − уровень игрока» (см. ClanExperienceService::percentForLevelDifference):
 *   ≤ −11 → 0%, [−10..0] → 1%, [1..9] → 3%, ≥ 10 → 5%.
 *
 * Симулятор прогоняет N игроков, которые постоянно фармят контент на
 * +level-offset уровней выше своего (по умолчанию +5, зона 3%) по H часов/день,
 * используя реальные формулы игры: опыт за моба, коэффициент разницы уровней
 * из AttackService и личный коэффициент опыта игрока. Количество убийств для
 * следующего уровня рассчитывается от фактически получаемого опыта, остаток
 * опыта после левелапа не теряется.
 *
 *   php artisan clan:simulate-experience
 *   php artisan clan:simulate-experience --players=6 --hours-per-day=3 --rounds-per-kill=8
 */
class SimulateClanExperience extends Command
{
    protected $signature = 'clan:simulate-experience
        {--players=10 : Число игроков в клане, одновременно фармящих}
        {--hours-per-day=5.5 : Часов фарма в день на игрока}
        {--rounds-per-kill=10 : Среднее число раундов боя на одного моба}
        {--sec-per-round=1.5 : Реальных секунд на раунд (клик + анимация)}
        {--level-offset=5 : На сколько уровней выше своего игроки фармят (≤0 = зона 1%, 1-9 = зона 3%, 10+ = зона 5%)}
        {--experience-multiplier=1 : Личный коэффициент опыта каждого игрока (учитывается и в опыте клана)}
        {--max-level=400 : Потолок уровня персонажа для симуляции}
        {--milestone-days=7,30,90,182,365,730,1095,1460,1825,2190 : Дни, на которые печатать срез (через запятую)}';

    protected $description = 'Симуляция накопления опыта клана по реальному темпу фарма — калибровка таблицы clan-level';

    public function handle(): int
    {
        $players = (int) $this->option('players');
        $hoursPerDay = (float) $this->option('hours-per-day');
        $roundsPerKill = (float) $this->option('rounds-per-kill');
        $secPerRound = (float) $this->option('sec-per-round');
        $levelOffset = (int) $this->option('level-offset');
        $experienceMultiplier = max(0.0, (float) $this->option('experience-multiplier'));
        $maxLevel = (int) $this->option('max-level');
        $milestoneDays = array_values(array_unique(array_filter(
            array_map('intval', explode(',', (string) $this->option('milestone-days'))),
            fn (int $day): bool => $day > 0,
        )));
        sort($milestoneDays);

        $clanRate = $this->clanRateForOffset($levelOffset);
        $secPerKill = $roundsPerKill * $secPerRound;

        if ($players < 1 || $hoursPerDay <= 0.0 || $secPerKill <= 0.0 || $maxLevel < 1 || $milestoneDays === []) {
            $this->error('Игроков, часов в день, времени на убийство и вех должен быть хотя бы один положительный параметр.');

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Игроков: %d | %.1fч/день | %.0f раундов/килл × %.1fс = %.0fс/килл | фарм +%d lvl (зона %.0f%% в клан) | личный опыт ×%.2f',
            $players, $hoursPerDay, $roundsPerKill, $secPerRound, $secPerKill, $levelOffset, $clanRate * 100, $experienceMultiplier,
        ));

        if ($clanRate <= 0.0 || $experienceMultiplier <= 0.0) {
            $this->warn('При выбранных параметрах игрок не получает опыт клана.');

            return self::FAILURE;
        }

        $secondsPerDay = $hoursPerDay * 3600;
        $elapsedSeconds = 0.0;
        $cumClanExp = 0.0;
        $milestoneIndex = 0;
        $rows = [];
        $playerExperience = 0;
        $nextLevelExperience = ExperienceCurve::expDiff(1);

        for ($level = 1; $level <= $maxLevel && $milestoneIndex < count($milestoneDays); $level++) {
            $experiencePerKill = $this->experiencePerKill($level, $levelOffset, $experienceMultiplier);
            $kills = (int) ceil(max(1, $nextLevelExperience - $playerExperience) / $experiencePerKill);
            $segmentSeconds = $kills * $secPerKill;
            $segmentClanExperience = $kills * round($experiencePerKill * $clanRate, 2) * $players;
            $segmentEndSeconds = $elapsedSeconds + $segmentSeconds;

            while ($milestoneIndex < count($milestoneDays)
                && $segmentEndSeconds >= $milestoneDays[$milestoneIndex] * $secondsPerDay) {
                $days = $milestoneDays[$milestoneIndex];
                $ratio = ($days * $secondsPerDay - $elapsedSeconds) / $segmentSeconds;
                $rows[] = [
                    $days,
                    sprintf('%.2f', $days / 365),
                    $level,
                    number_format($cumClanExp + $segmentClanExperience * $ratio, 0, '.', ' '),
                ];
                $milestoneIndex++;
            }

            $elapsedSeconds = $segmentEndSeconds;
            $cumClanExp += $segmentClanExperience;
            $playerExperience += $kills * $experiencePerKill;
            $nextLevelExperience += ExperienceCurve::expDiff($level + 1);
        }

        if ($milestoneIndex < count($milestoneDays)) {
            $this->warn(sprintf(
                'Достигнут потолок --max-level=%d раньше, чем все вехи — увеличьте --max-level.',
                $maxLevel,
            ));
        }

        $this->table(['День', 'Лет', 'Уровень игрока (~)', 'Накоплено опыта клана'], $rows);

        return self::SUCCESS;
    }

    private function experiencePerKill(int $playerLevel, int $levelOffset, float $experienceMultiplier): int
    {
        $monsterLevel = max(1, $playerLevel + $levelOffset);
        $monsterExperience = MonsterStatFormulas::expReward($monsterLevel, 1.0);
        $levelDifference = $playerLevel - $monsterLevel;
        $levelMultiplier = min(2.0, max(0.01, 1 - 0.05 * $levelDifference));
        $baseExperience = (int) round(max(1, $monsterExperience * $levelMultiplier));

        return max(1, (int) round($baseExperience * $experienceMultiplier));
    }

    private function clanRateForOffset(int $levelOffset): float
    {
        return match (true) {
            $levelOffset <= -11 => 0.0,
            $levelOffset <= 0 => 0.01,
            $levelOffset < 10 => 0.03,
            default => 0.05,
        };
    }
}
