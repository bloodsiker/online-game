<?php

use App\Modules\Structure\Exchange\Presentation\Http\ExchangeController;
use Illuminate\Support\Facades\Route;

Route::post('/exchange/{id}/apply', [ExchangeController::class, 'apply'])->name('exchange.apply');
Route::get('/exchange/{id}', [ExchangeController::class, 'index'])->name('exchange');