<?php

use App\Modules\Chat\Presentation\Http\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/chat', [ChatController::class, 'index'])->name('chat');
Route::get('/chat-text', [ChatController::class, 'chat'])->name('chat.text');
Route::get('/chat-log', [ChatController::class, 'chatLog'])->name('chat.log');
Route::get('/chat-action', [ChatController::class, 'chatAction'])->name('chat.action');
Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::post('/chat/ignore', [ChatController::class, 'addIgnore'])->name('chat.ignore.add');
Route::delete('/chat/ignore/{userId}', [ChatController::class, 'removeIgnore'])->name('chat.ignore.remove');
Route::get('/chat/ignores', [ChatController::class, 'ignoreList'])->name('chat.ignores');