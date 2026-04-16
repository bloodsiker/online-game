<?php

use App\Modules\Backpack\Presentation\Http\BackpackController;
use Illuminate\Support\Facades\Route;

Route::get('/backpack', [BackpackController::class, 'index'])->name('backpack');
Route::post('/backpack/order', [BackpackController::class, 'updateOrder'])->name('backpack.order');