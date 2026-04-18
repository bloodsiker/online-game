<?php

use App\Modules\Item\Presentation\Http\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/items/info/{id}', [ItemController::class, 'info'])->name('items.info');
Route::get('/items/put-on/{id}', [ItemController::class, 'putOn'])->name('items.put_on');
Route::get('/items/put-off/{id}', [ItemController::class, 'putOff'])->name('items.put_off');
Route::get('/items/pickup/{id}', [ItemController::class, 'pickUp'])->name('items.pick_up');
Route::get('/items/open-chest/{id}', [ItemController::class, 'openChest'])->name('items.open_chest');
Route::get('/items/view-chest/{id}', [ItemController::class, 'viewChest'])->name('items.view_chest');
Route::get('/items/pickup-chest/{chest}/{id}', [ItemController::class, 'pickUpInChest'])->name('items.pickup_chest');
Route::get('/items/hand-over/{id}', [ItemController::class, 'handOver'])->name('items.hand_over');
Route::get('/items/hand-over-to-user/{id}', [ItemController::class, 'handOverToUser'])->name('items.hand_over_to_user');
Route::get('/items/drop/{id}', [ItemController::class, 'dropItem'])->name('items.drop');
