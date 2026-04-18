<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DungeonController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\FightController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotbarController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\SlotController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/dd', [HomeController::class, 'gebug'])->name('gebug');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/map2', [HomeController::class, 'map2'])->name('map2');
Route::get('/map3', [HomeController::class, 'map3'])->name('map3');
Route::get('/clear', [HomeController::class, 'clear'])->name('clear');
Route::get('/login/{id}', [HomeController::class, 'login'])->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login')->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register')->withoutMiddleware([VerifyCsrfToken::class]);
Route::post('/register/check', [RegisterController::class, 'registerCheck'])->name('register.check');

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

Route::get('/error', [ErrorController::class, 'index'])->name('error');

Route::middleware(['updateLastOnline'])->group(function () {
    Route::post('/slots/update', [SlotController::class, 'updateSlot'])->name('slots.update');
    Route::get('/slots', [SlotController::class, 'index'])->name('slots');

    Route::get('/hotbar', [HotbarController::class, 'index'])->name('hotbar.index');
    Route::post('/hotbar/set', [HotbarController::class, 'set'])->name('hotbar.set');
    Route::post('/hotbar/use', [HotbarController::class, 'use'])->name('hotbar.use');
    Route::delete('/hotbar/clear/{slot}', [HotbarController::class, 'clear'])->name('hotbar.clear');

    Route::get('/fight/run-away/{id}', [FightController::class, 'runAway'])->name('fight.run-away');
    Route::get('/fight/attack/monster/{id}', [FightController::class, 'attackMonster'])->name('fight.attack.monster');
    Route::get('/fight/attack/{id}/{monsterId}/{action}', [FightController::class, 'attack'])->name('fight.attack');
    Route::get('/fight/{id}', [FightController::class, 'index'])->name('fight');

});

Route::get('/heal/{id}', [HealthController::class, 'index'])->name('heal');

// Dungeon routes
Route::get('/dungeons', [DungeonController::class, 'index'])->name('dungeon.index');
Route::get('/dungeon/{id}', [DungeonController::class, 'show'])->name('dungeon.show');
Route::post('/dungeon/{id}/enter', [DungeonController::class, 'enter'])->name('dungeon.enter');
Route::post('/dungeon/exit', [DungeonController::class, 'exit'])->name('dungeon.exit');

// Party routes
Route::get('/party', [PartyController::class, 'show'])->name('party.show');
Route::post('/party/create', [PartyController::class, 'create'])->name('party.create');
Route::post('/party/invite', [PartyController::class, 'invite'])->name('party.invite');
Route::post('/party/{partyId}/leave', [PartyController::class, 'leave'])->name('party.leave');
Route::delete('/party/{partyId}', [PartyController::class, 'disband'])->name('party.disband');

Route::get('/', [MainController::class, 'index'])->name('index');
