<?php

declare(strict_types=1);

use App\Modules\User\Presentation\Http\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/info/u/{id}', [UserController::class, 'info'])->name('info.user');
