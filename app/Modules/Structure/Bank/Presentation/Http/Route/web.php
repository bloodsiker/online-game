<?php

use App\Modules\Structure\Bank\Presentation\Http\BankController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/bank', [BankController::class, 'index'])->name('bank');
Route::get('/bank/lookup', [BankController::class, 'lookup'])->name('bank.lookup');