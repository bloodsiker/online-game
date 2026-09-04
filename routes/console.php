<?php

use App\Modules\Interface\Application\UseCases\ProcessDuePlayerStates;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('items:delete-expired-location')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::call(static fn (): int => app(ProcessDuePlayerStates::class)->execute(now()))
    ->name('players:process-state')
    // Реген налаштований раз на REGEN_INTERVAL=5с (див. Player::REGEN_INTERVAL),
    // щосекундний тік лише сканує таблиці без користі.
    ->everyFiveSeconds()
    ->withoutOverlapping(1);
