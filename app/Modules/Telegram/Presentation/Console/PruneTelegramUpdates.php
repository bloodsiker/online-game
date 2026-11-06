<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Console;

use App\Modules\Telegram\Domain\Contracts\TelegramUpdateRepository;
use Illuminate\Console\Command;

final class PruneTelegramUpdates extends Command
{
    protected $signature = 'telegram:prune';

    protected $description = 'Удалить истёкшие ссылки Telegram и ответы webhook старше 7 дней';

    public function handle(TelegramUpdateRepository $updates): int
    {
        $this->info('Удалено записей Telegram: '.$updates->prune());

        return self::SUCCESS;
    }
}
