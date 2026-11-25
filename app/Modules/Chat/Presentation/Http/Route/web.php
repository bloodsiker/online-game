<?php

use App\Modules\Chat\Presentation\Http\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->controller(ChatController::class)->group(function (): void {
    Route::get('/chat', 'index')->name('chat');
    Route::get('/chat-text', 'chat')->name('chat.text');
    Route::get('/chat-log', 'chatLog')->name('chat.log');
    Route::get('/chat-action', 'chatAction')->name('chat.action');
    Route::get('/chat/messages', 'messages')->name('chat.messages');
    Route::get('/chat/world-events', 'worldEvents')->name('chat.world-events');
    Route::get('/chat/party-availability', 'partyAvailability')->name('chat.party-availability');
    Route::post('/chat/send', 'send')->name('chat.send');
    Route::post('/chat/ignore', 'addIgnore')->name('chat.ignore.add');
    Route::delete('/chat/ignore/{userId}', 'removeIgnore')->name('chat.ignore.remove');
    Route::get('/chat/ignores', 'ignoreList')->name('chat.ignores');
});
