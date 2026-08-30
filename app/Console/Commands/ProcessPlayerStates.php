<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Interface\Application\UseCases\ProcessDuePlayerStates;
use Illuminate\Console\Command;

final class ProcessPlayerStates extends Command
{
    protected $signature = 'players:process-state';

    protected $description = 'Process due player regeneration and timed effects';

    public function handle(ProcessDuePlayerStates $processor): int
    {
        $processor->execute(now());

        return self::SUCCESS;
    }
}
