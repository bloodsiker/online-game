<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Application\Requests\CreateClanRequest;
use App\Modules\Clan\Application\UseCases\AddClanRole;
use App\Modules\Clan\Application\UseCases\CancelClanRequest;
use App\Modules\Clan\Application\UseCases\CreateClan;
use App\Modules\Clan\Application\UseCases\DeleteClanRole;
use App\Modules\Clan\Application\UseCases\GetClanIndexPage;
use App\Modules\Clan\Application\UseCases\GetClanInformationPage;
use App\Modules\Clan\Application\UseCases\GetClanLogsPage;
use App\Modules\Clan\Application\UseCases\GetClanMemberPage;
use App\Modules\Clan\Application\UseCases\GetClanMembersFrame;
use App\Modules\Clan\Application\UseCases\GetClanQuestsPage;
use App\Modules\Clan\Application\UseCases\GetClanRolePage;
use App\Modules\Clan\Application\UseCases\InviteToClan;
use App\Modules\Clan\Application\UseCases\KickClanMember;
use App\Modules\Clan\Application\UseCases\LeaveClan;
use App\Modules\Clan\Application\UseCases\SaveClanDescription;
use App\Modules\Clan\Application\UseCases\SaveClanMemberRoles;
use App\Modules\Clan\Application\UseCases\SaveClanNews;
use App\Modules\Clan\Application\UseCases\SaveClanRoles;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ClanController extends Controller
{
    public function __construct(
        private readonly GetClanMembersFrame $getClanMembersFrame,
        private readonly GetClanIndexPage $getClanIndexPage,
        private readonly GetClanMemberPage $getClanMemberPage,
        private readonly GetClanRolePage $getClanRolePage,
        private readonly CreateClan $createClan,
        private readonly CancelClanRequest $cancelClanRequest,
        private readonly InviteToClan $inviteToClan,
        private readonly LeaveClan $leaveClan,
        private readonly KickClanMember $kickClanMember,
        private readonly SaveClanMemberRoles $saveClanMemberRoles,
        private readonly AddClanRole $addClanRole,
        private readonly DeleteClanRole $deleteClanRole,
        private readonly SaveClanRoles $saveClanRoles,
        private readonly GetClanInformationPage $getClanInformationPage,
        private readonly SaveClanDescription $saveClanDescription,
        private readonly SaveClanNews $saveClanNews,
        private readonly GetClanQuestsPage $getClanQuestsPage,
        private readonly GetClanLogsPage $getClanLogsPage,
    ) {}

    public function membersFrame(): \Illuminate\View\View
    {
        $page = $this->getClanMembersFrame->execute(Auth::user());

        return view('interface::clan_frame', [
            'members' => $page->members,
            'clan' => $page->clan,
            'tenMinutesAgo' => $page->tenMinutesAgo,
        ]);
    }

    public function index(): \Illuminate\View\View
    {
        $page = $this->getClanIndexPage->execute(Auth::user());

        return view('clan::index', [
            'inClan' => $page->inClan,
            'activeQuests' => $page->activeQuests,
            'isLeader' => $page->isLeader,
            'registrarNpc' => $page->registrarNpc,
        ]);
    }

    public function member(): \Illuminate\View\View|RedirectResponse
    {
        try {
            $page = $this->getClanMemberPage->execute(Auth::user());
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::member', [
            'clan' => $page->clan,
            'rows' => $page->rows,
            'membership' => $page->membership,
            'allRoles' => $page->allRoles,
            'leaderRole' => $page->leaderRole,
            'onlineCount' => $page->onlineCount,
            'canKick' => $page->canKick,
            'canInvite' => $page->canInvite,
        ]);
    }

    public function role(): \Illuminate\View\View|RedirectResponse
    {
        try {
            $page = $this->getClanRolePage->execute(Auth::user());
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::role', [
            'clan' => $page->clan,
            'roles' => $page->roles,
            'membership' => $page->membership,
            'permissions' => $page->permissions,
            'canChangePerms' => $page->canChangePerms,
        ]);
    }

    public function store(CreateClanRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->clanMembership !== null) {
            session()->flash('message', 'Вы уже состоите в клане.');

            return redirect()->route('clan');
        }

        $this->createClan->execute($user, $request->input('name'), $request->file('logo'));

        session()->flash('message', 'Клан успешно создан!');

        return redirect()->route('clan.member');
    }

    public function cancelRequest(ClanJoinRequest $joinRequest): RedirectResponse
    {
        try {
            $this->cancelClanRequest->execute(Auth::user(), $joinRequest);
            session()->flash('message', 'Заявка отменена.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function invite(Request $request): RedirectResponse
    {
        try {
            $this->inviteToClan->execute(Auth::user(), $request->input('invite_nick'));
            session()->flash('message', 'Приглашение в клан отправлено');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function leaveClan(): RedirectResponse
    {
        try {
            $this->leaveClan->execute(Auth::user());
            session()->flash('message', 'Вы покинули клан.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan');
    }

    public function kickMember(User $target): RedirectResponse
    {
        try {
            $this->kickClanMember->execute(Auth::user(), $target);
            session()->flash('message', 'Игрок исключён из клана.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function saveMemberRoles(Request $request): RedirectResponse
    {
        try {
            $this->saveClanMemberRoles->execute(Auth::user(), $request->input('form.mem', []));
            session()->flash('message', 'Звания участников сохранены.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function addRole(Request $request): RedirectResponse
    {
        try {
            $this->addClanRole->execute(Auth::user(), $request->input('name'));
            session()->flash('message', 'Звание добавлено.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function deleteRole(ClanRole $role): RedirectResponse
    {
        try {
            $this->deleteClanRole->execute(Auth::user(), $role);
            session()->flash('message', 'Звание удалено.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function saveRoles(Request $request): RedirectResponse
    {
        try {
            $this->saveClanRoles->execute(Auth::user(), $request->input('form.grades', []));
            session()->flash('message', 'Полномочия сохранены.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function information(): \Illuminate\View\View|RedirectResponse
    {
        try {
            $page = $this->getClanInformationPage->execute(Auth::user());
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::information', [
            'clan' => $page->clan,
            'membership' => $page->membership,
            'canChangeNews' => $page->canChangeNews,
        ]);
    }

    public function saveDescription(Request $request): RedirectResponse
    {
        try {
            $this->saveClanDescription->execute(Auth::user(), $request->input('description', ''));
            session()->flash('message', 'Описание клана сохранено.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.information');
    }

    public function saveNews(Request $request): RedirectResponse
    {
        try {
            $this->saveClanNews->execute(
                Auth::user(),
                $request->input('news_1', ''),
                $request->input('news_2', ''),
                $request->input('news_3', ''),
            );
            session()->flash('message', 'Новости клана сохранены.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.information');
    }

    public function quests(Request $request): \Illuminate\View\View|RedirectResponse
    {
        try {
            $page = $this->getClanQuestsPage->execute(Auth::user());
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::quests', [
            'clan' => $page->clan,
            'isLeader' => $page->isLeader,
            'activeQuests' => $page->activeQuests,
            'availableQuests' => $page->availableQuests,
            'history' => $page->history,
            'membership' => $page->membership,
        ]);
    }

    public function logs(Request $request): \Illuminate\View\View|RedirectResponse
    {
        try {
            $page = $this->getClanLogsPage->execute(
                Auth::user(),
                $request->query('action'),
                $request->query('player'),
            );
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::logs', [
            'logs' => $page->logs,
            'actions' => $page->actions,
            'membership' => $page->membership,
            'filterAction' => $page->filterAction,
            'filterUser' => $page->filterUser,
        ]);
    }
}
