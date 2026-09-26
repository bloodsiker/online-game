<?php

declare(strict_types=1);

use App\Modules\Event\Presentation\Http\Admin\EventActivityController;
use App\Modules\Event\Presentation\Http\Admin\WorldEventController;
use Illuminate\Support\Facades\Route;

Route::get('/event/activities', [EventActivityController::class, 'index'])->name('event.activities');
Route::get('/event/activities/create', [EventActivityController::class, 'create'])->name('event.activity.create');
Route::post('/event/activities', [EventActivityController::class, 'store'])->name('event.activity.store');
Route::get('/event/activities/{activity}/edit', [EventActivityController::class, 'edit'])->name('event.activity.edit');
Route::post('/event/activities/{activity}', [EventActivityController::class, 'update'])->name('event.activity.update');
Route::get('/event/activities/{activity}/toggle', [EventActivityController::class, 'toggle'])->name('event.activity.toggle');
Route::get('/event/activities/{activity}/delete', [EventActivityController::class, 'delete'])->name('event.activity.delete');

Route::get('/event/world-events', [WorldEventController::class, 'index'])->name('event.world-events.index');
Route::get('/event/world-events/create', [WorldEventController::class, 'create'])->name('event.world-events.create');
Route::post('/event/world-events', [WorldEventController::class, 'store'])->name('event.world-events.store');
Route::post('/event/world-events/upload-image', [WorldEventController::class, 'uploadImage'])->name('event.world-events.upload-image');
Route::get('/event/world-events/{event}/edit', [WorldEventController::class, 'edit'])->name('event.world-events.edit');
Route::put('/event/world-events/{event}', [WorldEventController::class, 'update'])->name('event.world-events.update');
Route::post('/event/world-events/{event}/start', [WorldEventController::class, 'start'])->name('event.world-events.start');
Route::post('/event/world-events/{event}/finish', [WorldEventController::class, 'finish'])->name('event.world-events.finish');
Route::delete('/event/world-events/{event}', [WorldEventController::class, 'destroy'])->name('event.world-events.destroy');
