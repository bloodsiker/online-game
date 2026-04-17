<?php

namespace App\Console\Commands;

use App\Events\PlayerLeveledUp;
use App\Models\Player\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
    public function handle()
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

        $maxLevel = DB::table('experiences')->max('lvl');
        if ($targetLevel > $maxLevel) {
            $this->error("Максимальный уровень в игре: {$maxLevel}");

            return Command::FAILURE;
        }

        $this->info("Повышаем игрока {$player->id} с уровня {$currentLevel} до {$targetLevel}");
        $this->info('Всего уровней для прохождения: '.($targetLevel - $currentLevel));

        // 3. Последовательно повышаем каждый уровень
        $bar = $this->output->createProgressBar($targetLevel - $currentLevel);
        $bar->start();

        for ($level = $currentLevel + 1; $level <= $targetLevel; $level++) {
            $this->levelUp($player, $level);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✓ Игрок успешно повышен до {$targetLevel} уровня!");
        $this->info('Текущие характеристики:');
        $this->info("  HP: {$player->hp_now}/{$player->hp_max}");
        $this->info("  MP: {$player->mp_now}/{$player->mp_max}");
        $this->info("  STR: {$player->str}, AGIL: {$player->agil}, INT: {$player->int}");
        $this->info("  Опыт: {$player->exp}/{$player->exp_up}");

        return Command::SUCCESS;
    }

    /**
     * Повышение на один уровень
     */
    private function levelUp(Player $player, int $newLevel)
    {
        $levelConfig = DB::table('experiences')->where('lvl', $newLevel)->first();

        if (! $levelConfig) {
            throw new \Exception("Конфигурация для уровня {$newLevel} не найдена");
        }

        $nextLevelConfig = DB::table('experiences')->where('lvl', $newLevel + 1)->first();

        // Устанавливаем опыт и пороги
        $player->lvl = $newLevel;
        $player->exp = $levelConfig->exp;

        if ($nextLevelConfig) {
            $player->exp_up = $nextLevelConfig->exp;
            $player->exp_diff = $nextLevelConfig->exp_diff;
        } else {
            $player->exp_up = $levelConfig->exp;
            $player->exp_diff = 0;
        }

        // Сохраняем изменения
        $player->save();

        // Вызываем событие повышения уровня
        event(new PlayerLeveledUp($player));
    }
}
