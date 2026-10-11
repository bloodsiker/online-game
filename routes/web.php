<?php

use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::get('/dd', [HomeController::class, 'gebug'])->name('gebug');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/map/{map}/monsters', [HomeController::class, 'publicMapMonsters'])->name('map.monsters');
Route::get('/map/{map}/resources', [HomeController::class, 'publicMapResources'])->name('map.resources');
Route::get('/map/{slug}', [HomeController::class, 'publicMap'])->name('map.public');
Route::get('/map2', [HomeController::class, 'map2'])->name('map2');
Route::get('/map3', [HomeController::class, 'map3'])->name('map3');
Route::get('/map4', [HomeController::class, 'map4'])->name('map4');
Route::get('/map5', [HomeController::class, 'map5'])->name('map5');
Route::get('/map6', [HomeController::class, 'map6'])->name('map6');
Route::get('/map7', [HomeController::class, 'map7'])->name('map7');
Route::get('/map8', [HomeController::class, 'map8'])->name('map8');
Route::get('/clear', [HomeController::class, 'clear'])->name('clear');

Route::get('/login/{id}', [HomeController::class, 'login'])->name('login.as');

// // ======== Ручная настройка верификации ======== //
//
// // Показ уведомления о необходимости подтвердить email
// Route::get('/email/verify', function () {
//    return view('auth.verify');
// })->middleware('auth')->name('verification.notice');
//
// // Обработка подтверждения email по ссылке
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//    $request->fulfill();
//    return redirect('/home')->with('success', 'Email успешно подтвержден!');
// })->middleware(['auth', 'signed'])->name('verification.verify');
//
// // Повторная отправка письма с подтверждением
// Route::post('/email/verification-notification', function (Request $request) {
//    $request->user()->sendEmailVerificationNotification();
//    return back()->with('resent', true);
// })->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

// Легаси-адрес прода: старые ссылки и закэшированный JS ведут на artifact_info.php
Route::get('/artifact_info.php', function (): mixed {
    $id = (int) request()->query('artifact_id');
    abort_if($id < 1, 404);

    return redirect()->route('items.info', ['id' => $id]);
});

Route::middleware('auth')->group(function (): void {});
