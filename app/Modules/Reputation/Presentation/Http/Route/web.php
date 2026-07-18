<?php

declare(strict_types=1);

use App\Modules\Reputation\Presentation\Http\ReputationController;
use Illuminate\Support\Facades\Route;

Route::get('/reputations', [ReputationController::class, 'list'])->name('reputation.list');
Route::get('/reputation/{id}', [ReputationController::class, 'index'])->name('reputation.index');
Route::post('/reputation/{id}/take', [ReputationController::class, 'take'])->name('reputation.take');
Route::post('/reputation/{id}/decline', [ReputationController::class, 'decline'])->name('reputation.decline');
Route::get('/reputation/{id}/shop', [ReputationController::class, 'shop'])->name('reputation.shop');
Route::post('/reputation/{id}/shop/add-cart', [ReputationController::class, 'addCart'])->name('reputation.add_cart');
Route::get('/reputation/{id}/shop/delete-cart/{cartId}', [ReputationController::class, 'deleteCart'])->name('reputation.delete_cart');
Route::get('/reputation/{id}/shop/clear-cart', [ReputationController::class, 'clearCart'])->name('reputation.clear_cart');
Route::post('/reputation/{id}/shop/buy', [ReputationController::class, 'buy'])->name('reputation.buy');
