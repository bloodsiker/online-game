<?php

use App\Modules\Event\Presentation\Http\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/events', [EventController::class, 'index'])->name('events');