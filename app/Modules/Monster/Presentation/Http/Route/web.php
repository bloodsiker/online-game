<?php

declare(strict_types=1);

use App\Modules\Monster\Presentation\Http\MonsterController;
use Illuminate\Support\Facades\Route;

Route::get('/info/m/{id}', [MonsterController::class, 'info'])->name('info.monster');
