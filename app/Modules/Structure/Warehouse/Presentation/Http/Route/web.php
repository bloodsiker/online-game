<?php

use App\Modules\Structure\Warehouse\Presentation\Http\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/warehouse/{id}/take-item', [WarehouseController::class, 'takeItem'])->name('warehouse.take_item');
Route::match(['GET', 'POST'], '/warehouse/{id}', [WarehouseController::class, 'index'])->name('warehouse');
