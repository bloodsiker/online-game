<?php

declare(strict_types=1);

use App\Modules\Event\Presentation\Http\Admin\EventActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/event/activities', [EventActivityController::class, 'index'])->name('event.activities');
Route::get('/event/activities/create', [EventActivityController::class, 'create'])->name('event.activity.create');
Route::post('/event/activities', [EventActivityController::class, 'store'])->name('event.activity.store');
Route::get('/event/activities/{activity}/edit', [EventActivityController::class, 'edit'])->name('event.activity.edit');
Route::post('/event/activities/{activity}', [EventActivityController::class, 'update'])->name('event.activity.update');
Route::get('/event/activities/{activity}/toggle', [EventActivityController::class, 'toggle'])->name('event.activity.toggle');
Route::get('/event/activities/{activity}/delete', [EventActivityController::class, 'delete'])->name('event.activity.delete');
