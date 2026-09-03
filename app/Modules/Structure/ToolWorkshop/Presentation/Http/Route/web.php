<?php

declare(strict_types=1);

use App\Modules\Structure\ToolWorkshop\Presentation\Http\ToolWorkshopController;
use Illuminate\Support\Facades\Route;

Route::get('/tool-workshop/{id}', [ToolWorkshopController::class, 'index'])->name('tool_workshop');
Route::post('/tool-workshop/{id}/upgrade', [ToolWorkshopController::class, 'upgrade'])->name('tool_workshop.upgrade.process');
