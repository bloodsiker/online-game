<?php

use App\Modules\Item\Presentation\Http\ItemController;
use App\Modules\Item\Presentation\Http\LockpickingController;
use Illuminate\Support\Facades\Route;

Route::get('/item/info/{id}', [ItemController::class, 'info'])->name('items.info');
Route::get('/item/info/s/{id}', [ItemController::class, 'infoByShareItem'])->name('items.info.share');

Route::middleware(['auth'])->group(function (): void {
    Route::post('/items/use/{id}', [ItemController::class, 'useItem'])->name('items.use');
    Route::get('/items/put-on/{id}', [ItemController::class, 'putOn'])->name('items.put_on');
    Route::get('/items/put-off/{id}', [ItemController::class, 'putOff'])->name('items.put_off');
    Route::get('/items/pickup/{id}', [ItemController::class, 'pickUp'])->name('items.pick_up');
    Route::post('/items/open-chest/{id}', [ItemController::class, 'openChest'])->name('items.open_chest');
    Route::get('/items/view-chest/{id}', [ItemController::class, 'viewChest'])->name('items.view_chest');
    Route::post('/items/pickup-chest/{chest}/{id}', [ItemController::class, 'pickUpInChest'])->name('items.pickup_chest');
    Route::get('/items/hand-over/{id}', [ItemController::class, 'handOver'])->name('items.hand_over');
    Route::get('/items/hand-over-to-user/{id}', [ItemController::class, 'handOverToUser'])->name('items.hand_over_to_user');
    Route::get('/items/drop/{id}', [ItemController::class, 'dropItem'])->name('items.drop');
    Route::get('/items/lockpick/{id}', [LockpickingController::class, 'show'])->name('items.lockpick.show');
    Route::post('/items/lockpick/{id}/start', [LockpickingController::class, 'start'])->name('items.lockpick.start');
    Route::post('/items/lockpick/{id}/complete', [LockpickingController::class, 'complete'])->name('items.lockpick.complete');
    Route::post('/items/lockpick/{id}/cancel', [LockpickingController::class, 'cancel'])->name('items.lockpick.cancel');
});
