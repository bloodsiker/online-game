<?php

declare(strict_types=1);

use App\Modules\Party\Presentation\Http\PartyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/who/party', [PartyController::class, 'frame'])->name('who.party');
    Route::get('/party', [PartyController::class, 'show'])->name('party.show');
    Route::post('/party/create', [PartyController::class, 'create'])->name('party.create');
    Route::post('/party/invite', [PartyController::class, 'invite'])->name('party.invite');
    Route::get('/party/accept/{inviteUuid}', [PartyController::class, 'accept'])->whereUuid('inviteUuid')->name('party.accept');
    Route::get('/party/decline/{inviteUuid}', [PartyController::class, 'decline'])->whereUuid('inviteUuid')->name('party.decline');
    Route::post('/party/{partyId}/kick', [PartyController::class, 'kick'])->name('party.kick');
    Route::post('/party/{partyId}/leave', [PartyController::class, 'leave'])->name('party.leave');
    Route::delete('/party/{partyId}', [PartyController::class, 'disband'])->name('party.disband');
});
