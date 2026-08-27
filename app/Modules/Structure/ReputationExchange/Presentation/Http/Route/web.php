<?php

use App\Modules\Structure\ReputationExchange\Presentation\Http\ReputationExchangeController;
use Illuminate\Support\Facades\Route;

Route::post('/reputation-exchange/{id}/apply', [ReputationExchangeController::class, 'apply'])->name('reputation_exchange.apply');
Route::get('/reputation-exchange/{id}', [ReputationExchangeController::class, 'index'])->name('reputation_exchange');
