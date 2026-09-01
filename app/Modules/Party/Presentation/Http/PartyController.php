<?php

declare(strict_types=1);

namespace App\Modules\Party\Presentation\Http;

use App\Modules\Party\Application\Services\PartyService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class PartyController extends Controller
{
    public function __construct(
        private readonly PartyService $partyService,
    ) {}

    /** Панель группы внутри who-frame. */
    public function frame(): View
    {
        return view('party::frame', [
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

            return $this->backToFrame('Группа создана.');
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
            $invited = $this->partyService->inviteByName(
                partyId: (int) $request->input('party_id'),
                name: (string) $request->input('name'),
            );

            return $this->backToFrame('Приглашение отправлено игроку '.$invited->name.'.');
        } catch (RuntimeException $e) {
            return $this->backToFrame(error: $e->getMessage());
        }
    }

    /** Принятие приглашения из чата (переход по ссылке из iframe чата). */
    public function accept(Request $request, string $inviteUuid): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $this->partyService->acceptInvite($user, $inviteUuid);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Вы вступили в группу.']);
            }

            return redirect()
                ->route('game')
                ->with('party_success', 'Вы вступили в группу.');
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()
                ->route('game')
                ->with('party_error', $e->getMessage());
        }
    }

    /** Отклонение приглашения из чата. */
    public function decline(Request $request, string $inviteUuid): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $this->partyService->declineInvite($user, $inviteUuid);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Приглашение отклонено.']);
            }

            return redirect()
                ->route('game')
                ->with('party_success', 'Приглашение отклонено.');
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()
                ->route('game')
                ->with('party_error', $e->getMessage());
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

            return $this->backToFrame('Группа распущена.');
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
