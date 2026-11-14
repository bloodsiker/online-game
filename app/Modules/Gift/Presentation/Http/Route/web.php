<?php

declare(strict_types=1);

use App\Modules\Gift\Presentation\Http\GiftController;
use Illuminate\Support\Facades\Route;

Route::post('/gifts/send', [GiftController::class, 'send'])->middleware(['auth', 'throttle:20,1'])->name('gifts.send');
Route::get('/info/u/{id}/gifts', [GiftController::class, 'index'])->whereNumber('id')->name('gifts.index');
