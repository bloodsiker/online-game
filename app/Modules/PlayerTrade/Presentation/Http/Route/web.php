<?php

declare(strict_types=1);

use App\Modules\PlayerTrade\Presentation\Http\PlayerTradeController;
use Illuminate\Support\Facades\Route;

Route::post('/player-trade/invite/{user}', [PlayerTradeController::class, 'invite'])->whereNumber('user')->name('player-trade.invite');
Route::get('/player-trade/{uuid}', [PlayerTradeController::class, 'show'])->whereUuid('uuid')->name('player-trade.show');
Route::post('/player-trade/{uuid}/accept', [PlayerTradeController::class, 'accept'])->whereUuid('uuid')->name('player-trade.accept');
Route::post('/player-trade/{uuid}/decline', [PlayerTradeController::class, 'decline'])->whereUuid('uuid')->name('player-trade.decline');
Route::get('/player-trade/{uuid}/state', [PlayerTradeController::class, 'state'])->whereUuid('uuid')->name('player-trade.state');
Route::get('/player-trade/{uuid}/tooltip/{item}', [PlayerTradeController::class, 'tooltip'])->whereUuid('uuid')->whereNumber('item')->name('player-trade.tooltip');
Route::put('/player-trade/{uuid}/offer', [PlayerTradeController::class, 'saveOffer'])->whereUuid('uuid')->name('player-trade.offer');
Route::post('/player-trade/{uuid}/confirm', [PlayerTradeController::class, 'confirm'])->whereUuid('uuid')->name('player-trade.confirm');
Route::post('/player-trade/{uuid}/cancel', [PlayerTradeController::class, 'cancel'])->whereUuid('uuid')->name('player-trade.cancel');
