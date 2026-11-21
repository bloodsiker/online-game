<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Console;

use App\Modules\Payments\Infrastructure\Nbu\NbuExchangeRate;
use Illuminate\Console\Command;

final class SyncNbuRate extends Command
{
    protected $signature = 'payments:sync-nbu-rate';

    protected $description = 'Загрузить курс USD/UAH НБУ на сегодня в историю курсов';

    public function handle(NbuExchangeRate $rates): int
    {
        $this->info('Курс НБУ: '.number_format($rates->sync(), 4, '.', '').' грн за 1 USD');

        return self::SUCCESS;
    }
}
