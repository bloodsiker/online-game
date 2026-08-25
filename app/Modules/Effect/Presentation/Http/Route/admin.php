<?php

declare(strict_types=1);

use App\Modules\Effect\Presentation\Http\Admin\EffectController;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/effect/create', [EffectController::class, 'create'])->name('effect.create');
Route::match(['GET', 'POST'], '/effect/{effect}', [EffectController::class, 'info'])->name('effect.info');
Route::get('/effects', [EffectController::class, 'list'])->name('effects');
