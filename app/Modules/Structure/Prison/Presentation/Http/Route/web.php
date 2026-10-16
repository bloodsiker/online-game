<?php

use App\Modules\Structure\Prison\Presentation\Http\PrisonController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'prison.context'])->get('/prison/{id}', [PrisonController::class, 'index'])->name('prison.index');
Route::middleware(['auth', 'prison.context'])->post('/prison/{id}/buyout', [PrisonController::class, 'buyOut'])->name('prison.buyout');
