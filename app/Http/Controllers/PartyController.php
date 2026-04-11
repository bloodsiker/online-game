<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PartyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PartyController extends Controller
{
    public function __construct(
        private readonly PartyService $partyService,
    ) {}

    public function show(): JsonResponse
    {
        $party = $this->partyService->getMyParty();

        return response()->json($party);
    }

    public function create(Request $request): RedirectResponse
    {
        try {
            $party = $this->partyService->createParty(
                maxSize: (int) $request->input('max_size', 4),
            );

            return back()->with('success', 'Группа создана.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['party' => $e->getMessage()]);
        }
    }

    public function invite(Request $request): RedirectResponse
    {
        $request->validate(['party_id' => 'required|integer', 'user_id' => 'required|integer']);

        try {
            $this->partyService->invite(
                partyId:      (int) $request->input('party_id'),
                targetUserId: (int) $request->input('user_id'),
            );

            return back()->with('success', 'Игрок приглашён.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['party' => $e->getMessage()]);
        }
    }

    public function leave(int $partyId): RedirectResponse
    {
        try {
            $this->partyService->leave($partyId);
            return back()->with('success', 'Вы покинули группу.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['party' => $e->getMessage()]);
        }
    }

    public function disband(int $partyId): RedirectResponse
    {
        try {
            $this->partyService->disband($partyId);
            return back()->with('success', 'Группа распущена.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['party' => $e->getMessage()]);
        }
    }
}