<?php

use App\Modules\Structure\Auction\Presentation\Http\AuctionController;
use Illuminate\Support\Facades\Route;

Route::get('/auction/{id}/buyItem/{itemId}', [AuctionController::class, 'buyItem'])->name('auction.buy_item');
Route::match(['GET', 'POST'], '/auction/{id}/my-lot/edit/{slotId}', [AuctionController::class, 'myLotEdit'])->name('auction.my_lot.edit');
Route::get('/auction/{id}/my-lot/cancel/{slotId}', [AuctionController::class, 'myLotCancel'])->name('auction.my_lot.cancel');
Route::get('/auction/{id}/my-lot', [AuctionController::class, 'myLot'])->name('auction.my_lot');
Route::post('/auction/{id}/new-lot/save', [AuctionController::class, 'newLotSave'])->name('auction.new_lot.save');
Route::get('/auction/{id}/new-lot', [AuctionController::class, 'newLot'])->name('auction.new_lot');
Route::get('/auction/{id}/exchange', [AuctionController::class, 'exchange'])->name('auction.exchange');
Route::get('/auction/{id}/my-orders', [AuctionController::class, 'myOrders'])->name('auction.my_orders');
Route::post('/auction/{id}/new-order/save', [AuctionController::class, 'newOrderSave'])->name('auction.new_order.save');
Route::get('/auction/{id}/new-order', [AuctionController::class, 'newOrder'])->name('auction.new_order');
Route::get('/auction/{id}/order/cancel/{orderId}', [AuctionController::class, 'cancelOrder'])->name('auction.order.cancel');
Route::get('/auction/{id}/order/fulfill/{orderId}', [AuctionController::class, 'fulfillOrder'])->name('auction.order.fulfill');
Route::get('/auction/{id}/claims', [AuctionController::class, 'claims'])->name('auction.claims');
Route::get('/auction/{id}/claim/take/{claimId}', [AuctionController::class, 'claimTake'])->name('auction.claim.take');
Route::get('/auction/{id}', [AuctionController::class, 'index'])->name('auction');