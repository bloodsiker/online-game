<?php

declare(strict_types=1);

use App\Modules\Interface\Presentation\Http\InterfaceController;
use Illuminate\Support\Facades\Route;

Route::get('/on-map', [InterfaceController::class, 'onMap'])->name('on_map');
Route::get('/menu', [InterfaceController::class, 'menu'])->name('menu');
Route::get('/who', [InterfaceController::class, 'who'])->name('who');
Route::get('/hero', [InterfaceController::class, 'hero'])->name('hero');
Route::get('/game', [InterfaceController::class, 'game'])->name('game');
Route::get('/interface', [InterfaceController::class, 'interface'])->name('interface');
Route::post('/player/heartbeat', [InterfaceController::class, 'heartbeat'])
    ->middleware(['auth', 'throttle:12,1'])
    ->name('player.heartbeat');
