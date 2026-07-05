<?php

use App\Modules\Backpack\Presentation\Http\BackpackController;
use Illuminate\Support\Facades\Route;

Route::get('/backpack', [BackpackController::class, 'index'])->name('backpack');
Route::get('/backpack/equip', [BackpackController::class, 'equip'])->name('backpack.equip');
Route::get('/backpack/bag', [BackpackController::class, 'bag'])->name('backpack.bag');
Route::post('/backpack/order', [BackpackController::class, 'updateOrder'])->name('backpack.order');
