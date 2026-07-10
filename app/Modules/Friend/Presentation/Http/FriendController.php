<?php

declare(strict_types=1);

namespace App\Modules\Friend\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Friend\Application\UseCases\AcceptFriend;
use App\Modules\Friend\Application\UseCases\AddEnemy;
use App\Modules\Friend\Application\UseCases\AddFriend;
use App\Modules\Friend\Application\UseCases\AddIgnore;
use App\Modules\Friend\Application\UseCases\DeclineFriend;
use App\Modules\Friend\Application\UseCases\GetFriendsFrame;
use App\Modules\Friend\Application\UseCases\GetFriendsPage;
use App\Modules\Friend\Application\UseCases\RemoveEnemy;
use App\Modules\Friend\Application\UseCases\RemoveFriend;
use App\Modules\Friend\Application\UseCases\RemoveIgnore;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FriendController extends Controller
{
    public function __construct(
        private readonly GetFriendsFrame $getFriendsFrame,
        private readonly GetFriendsPage $getFriendsPage,
        private readonly AddFriend $addFriend,
        private readonly AcceptFriend $acceptFriend,
        private readonly DeclineFriend $declineFriend,
        private readonly RemoveFriend $removeFriend,
        private readonly AddEnemy $addEnemy,
        private readonly RemoveEnemy $removeEnemy,
        private readonly AddIgnore $addIgnore,
        private readonly RemoveIgnore $removeIgnore,
    ) {}

    public function friendsFrame(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('friend::frame', [
            'frame' => $this->getFriendsFrame->execute($user),
        ]);
    }

    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('friend::index', [
            'page' => $this->getFriendsPage->execute($user),
        ]);
    }

    public function addFriend(Request $request): RedirectResponse|JsonResponse
    {
        $result = $this->addFriend->execute(auth()->user(), (string) $request->input('name', ''));

        if ($request->expectsJson()) {
            return response()->json(['ok' => $result->ok, 'message' => $result->message]);
        }

        return $this->backWith($result);
    }

    public function acceptFriend(int $relationship): RedirectResponse
    {
        return $this->backWith($this->acceptFriend->execute(auth()->user(), $relationship));
    }

    public function declineFriend(int $relationship): RedirectResponse
    {
        return $this->backWith($this->declineFriend->execute(auth()->user(), $relationship));
    }

    public function removeFriend(int $relationship): RedirectResponse
    {
        return $this->backWith($this->removeFriend->execute(auth()->user(), $relationship));
    }

    public function addEnemy(Request $request): RedirectResponse
    {
        return $this->backWith($this->addEnemy->execute(auth()->user(), (string) $request->input('name', '')));
    }

    public function removeEnemy(int $relationship): RedirectResponse
    {
        return $this->backWith($this->removeEnemy->execute(auth()->user(), $relationship));
    }

    public function addIgnore(Request $request): RedirectResponse
    {
        return $this->backWith($this->addIgnore->execute(auth()->user(), (string) $request->input('name', '')));
    }

    public function removeIgnore(int $relationship): RedirectResponse
    {
        return $this->backWith($this->removeIgnore->execute(auth()->user(), $relationship));
    }

    private function backWith($result): RedirectResponse
    {
        if ($result->message === '') {
            return redirect()->back();
        }

        return redirect()->back()->with($result->flashType, $result->message);
    }
}
