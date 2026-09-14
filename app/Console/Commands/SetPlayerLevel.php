<?php

namespace App\Console\Commands;

use App\Modules\Player\Domain\Services\PlayerLevelUpService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Console\Command;

class SetPlayerLevel extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'player:set-level
                            {id : ID игрока}
                            {level : Целевой уровень}';

    /**
     * Описание команды.
     *
     * @var string
     */
    protected $description = 'Последовательно повышает уровень игрока с пересчетом характеристик';

    /**
     * Выполнение команды.
     */
    public function handle(PlayerLevelUpService $levelUpService)
    {
        $playerId = $this->argument('id');
        $targetLevel = (int) $this->argument('level');

        // 1. Находим игрока
        $player = Player::find($playerId);

        if (! $player) {
            $this->error("Игрок с ID {$playerId} не найден.");

            return Command::FAILURE;
        }

        $currentLevel = $player->lvl;

        // 2. Проверки
        if ($targetLevel <= $currentLevel) {
            $this->error("Целевой уровень ({$targetLevel}) должен быть выше текущего ({$currentLevel})");

            return Command::FAILURE;
        }

        $maxLevel = $levelUpService->maxLevel();
        if ($targetLevel > $maxLevel) {
            $this->error("Максимальный уровень в игре: {$maxLevel}");

            return Command::FAILURE;
        }

        $this->info("Повышаем игрока {$player->id} с уровня {$currentLevel} до {$targetLevel}");
        $this->info('Всего уровней для прохождения: '.($targetLevel - $currentLevel));

        // 3. Последовательно повышаем каждый уровень
        $bar = $this->output->createProgressBar($targetLevel - $currentLevel);
        $bar->start();
        $player = $levelUpService->raiseToLevel($player, $targetLevel, fn () => $bar->advance());
        $bar->finish();
        $this->newLine();

        $this->info("✓ Игрок успешно повышен до {$targetLevel} уровня!");
        $this->info('Текущие характеристики:');
        $this->info("  HP: {$player->hp_now}/{$player->hp_max}");
        $this->info("  MP: {$player->mp_now}/{$player->mp_max}");
        $this->info("  STR: {$player->strength}, AGIL: {$player->agility}, INT: {$player->intelligence}");
        $this->info("  Опыт: {$player->exp}/{$player->exp_up}");

        return Command::SUCCESS;
    }
}
