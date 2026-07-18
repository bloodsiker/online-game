<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PartyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PartyController extends Controller
{
    public function __construct(
        private readonly PartyService $partyService,
    ) {}

    /** Панель группы внутри who-frame. */
    public function frame(): View
    {
        return view('party.frame', [
            'party' => $this->partyService->getMyParty(),
            'maxSize' => PartyService::MAX_SIZE,
            'myUserId' => (int) auth()->id(),
        ]);
    }

    public function show(): JsonResponse
    {
        $party = $this->partyService->getMyParty();

        return response()->json($party);
    }

    public function create(Request $request): RedirectResponse
    {
        try {
            $this->partyService->createParty(
                maxSize: (int) $request->input('max_size', PartyService::MAX_SIZE),
            );

            return $this->backToFrame();
        } catch (RuntimeException $e) {
            return $this->backToFrame(error: $e->getMessage());
        }
    }

    public function invite(Request $request): RedirectResponse
    {
        $request->validate([
            'party_id' => 'required|integer',
            'name' => 'required|string|max:50',
        ]);

        try {
            $this->partyService->inviteByName(
                partyId: (int) $request->input('party_id'),
                name: (string) $request->input('name'),
            );

            return $this->backToFrame();
        } catch (RuntimeException $e) {
            return $this->backToFrame(error: $e->getMessage());
        }
    }

    public function kick(Request $request, int $partyId): RedirectResponse
    {
        $request->validate(['user_id' => 'required|integer']);

        try {
            $this->partyService->kick($partyId, (int) $request->input('user_id'));

            return $this->backToFrame('Игрок исключён из группы.');
        } catch (RuntimeException $e) {
            return $this->backToFrame(error: $e->getMessage());
        }
    }

    public function leave(int $partyId): RedirectResponse
    {
        try {
            $this->partyService->leave($partyId);

            return $this->backToFrame('Вы покинули группу.');
        } catch (RuntimeException $e) {
            return $this->backToFrame(error: $e->getMessage());
        }
    }

    public function disband(int $partyId): RedirectResponse
    {
        try {
            $this->partyService->disband($partyId);

            return $this->backToFrame();
        } catch (RuntimeException $e) {
            return $this->backToFrame(error: $e->getMessage());
        }
    }

    private function backToFrame(?string $success = null, ?string $error = null): RedirectResponse
    {
        $redirect = redirect()->route('who.party');

        if ($success !== null) {
            $redirect->with('party_success', $success);
        }

        if ($error !== null) {
            $redirect->with('party_error', $error);
        }

        return $redirect;
    }
}
