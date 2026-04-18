<?php

declare(strict_types=1);

use App\Modules\Friend\Presentation\Http\FriendController;
use Illuminate\Support\Facades\Route;

Route::get('/friends', [FriendController::class, 'index'])->name('friends');
Route::post('/friends/add', [FriendController::class, 'addFriend'])->name('friends.add');
Route::post('/friends/{relationship}/accept', [FriendController::class, 'acceptFriend'])->name('friends.accept');
Route::post('/friends/{relationship}/decline', [FriendController::class, 'declineFriend'])->name('friends.decline');
Route::delete('/friends/{relationship}', [FriendController::class, 'removeFriend'])->name('friends.remove');
Route::post('/enemies/add', [FriendController::class, 'addEnemy'])->name('enemies.add');
Route::delete('/enemies/{relationship}', [FriendController::class, 'removeEnemy'])->name('enemies.remove');
Route::post('/ignores/add', [FriendController::class, 'addIgnore'])->name('ignores.add');
Route::delete('/ignores/{relationship}', [FriendController::class, 'removeIgnore'])->name('ignores.remove');
Route::get('/who/friends', [FriendController::class, 'friendsFrame'])->name('who.friends');
