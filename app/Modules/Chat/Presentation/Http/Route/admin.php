<?php

declare(strict_types=1);

use App\Modules\Chat\Presentation\Http\Admin\ChatAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/chat', [ChatAdminController::class, 'index'])->name('chat.index');
Route::post('/chat/system-message', [ChatAdminController::class, 'send'])->name('chat.send');
