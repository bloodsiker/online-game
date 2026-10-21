<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Application\Requests\CreateClanRequest;
use App\Modules\Clan\Application\Services\ClanApplicationService;
use App\Modules\Clan\Application\UseCases\AcceptClanInvite;
use App\Modules\Clan\Application\UseCases\AddClanRole;
use App\Modules\Clan\Application\UseCases\CancelClanRequest;
use App\Modules\Clan\Application\UseCases\CreateClan;
use App\Modules\Clan\Application\UseCases\DeclineClanInvite;
use App\Modules\Clan\Application\UseCases\DeleteClanRole;
use App\Modules\Clan\Application\UseCases\GetClanBossPage;
use App\Modules\Clan\Application\UseCases\GetClanCharacteristicsPage;
use App\Modules\Clan\Application\UseCases\GetClanDirectoryPage;
use App\Modules\Clan\Application\UseCases\GetClanIndexPage;
use App\Modules\Clan\Application\UseCases\GetClanInformationPage;
use App\Modules\Clan\Application\UseCases\GetClanLogsPage;
use App\Modules\Clan\Application\UseCases\GetClanMemberPage;
use App\Modules\Clan\Application\UseCases\GetClanMembersFrame;
use App\Modules\Clan\Application\UseCases\GetClanQuestsPage;
use App\Modules\Clan\Application\UseCases\GetClanRolePage;
use App\Modules\Clan\Application\UseCases\GetPublicClanPage;
use App\Modules\Clan\Application\UseCases\InviteToClan;
use App\Modules\Clan\Application\UseCases\KickClanMember;
use App\Modules\Clan\Application\UseCases\LeaveClan;
use App\Modules\Clan\Application\UseCases\SaveClanDescription;
use App\Modules\Clan\Application\UseCases\SaveClanMemberRoles;
use App\Modules\Clan\Application\UseCases\SaveClanNews;
use App\Modules\Clan\Application\UseCases\SaveClanRoles;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class ClanController extends Controller
{
    public function __construct(
        private readonly GetClanMembersFrame $getClanMembersFrame,
        private readonly GetClanIndexPage $getClanIndexPage,
        private readonly GetClanDirectoryPage $getClanDirectoryPage,
        private readonly ClanApplicationService $clanApplicationService,
        private readonly GetClanMemberPage $getClanMemberPage,
        private readonly GetClanRolePage $getClanRolePage,
        private readonly CreateClan $createClan,
        private readonly CancelClanRequest $cancelClanRequest,
        private readonly InviteToClan $inviteToClan,
        private readonly AcceptClanInvite $acceptClanInvite,
        private readonly DeclineClanInvite $declineClanInvite,
        private readonly LeaveClan $leaveClan,
        private readonly KickClanMember $kickClanMember,
        private readonly SaveClanMemberRoles $saveClanMemberRoles,
        private readonly AddClanRole $addClanRole,
        private readonly DeleteClanRole $deleteClanRole,
        private readonly SaveClanRoles $saveClanRoles,
        private readonly GetClanInformationPage $getClanInformationPage,
        private readonly GetClanCharacteristicsPage $getClanCharacteristicsPage,
        private readonly SaveClanDescription $saveClanDescription,
        private readonly SaveClanNews $saveClanNews,
        private readonly GetClanQuestsPage $getClanQuestsPage,
        private readonly GetClanBossPage $getClanBossPage,
        private readonly GetClanLogsPage $getClanLogsPage,
        private readonly GetPublicClanPage $getPublicClanPage,
    ) {}

    public function membersFrame(): View
    {
        $page = $this->getClanMembersFrame->execute(Auth::user());

        return view('interface::clan_frame', [
            'members' => $page->members,
            'clan' => $page->clan,
            'tenMinutesAgo' => $page->tenMinutesAgo,
        ]);
    }

    public function publicInfo(Request $request, Clan $clan): View
    {
        $page = $this->getPublicClanPage->execute($clan, $request->query('mode'));

        return view('clan::public', [
            'clan' => $page->clan,
            'mode' => $page->mode,
            'membersCount' => $page->membersCount,
            'levelRank' => $page->levelRank,
            'experienceRank' => $page->experienceRank,
            'members' => $page->members,
            'learnedSkills' => $page->learnedSkills,
            'logs' => $page->logs,
        ]);
    }

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user->clanMembership === null && (int) $user->location_id !== 29) {
            session()->flash('message', __('clan.directory.location_required'));

            return redirect()->route('clan.rating');
        }

        $page = $this->getClanIndexPage->execute($user);

        return view('clan::index', [
            'inClan' => $page->inClan,
            'activeQuests' => $page->activeQuests,
            'isLeader' => $page->isLeader,
            'registrarNpc' => $page->registrarNpc,
        ]);
    }

    public function rating(): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user->clanMembership !== null) {
            return redirect()->route('clan.member');
        }

        $page = $this->getClanDirectoryPage->execute($user);

        return view('clan::rating', [
            'clans' => $page->clans,
            'applications' => $page->applications,
        ]);
    }

    public function apply(Clan $clan): RedirectResponse
    {
        try {
            $this->clanApplicationService->apply(Auth::user(), $clan);
            session()->flash('message', __('clan.application.submitted'));
        } catch (RuntimeException $exception) {
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->route('clan.rating');
    }

    public function acceptApplication(ClanJoinRequest $application): RedirectResponse
    {
        try {
            $this->clanApplicationService->accept(Auth::user(), $application);
            session()->flash('message', __('clan.application.accepted'));
        } catch (RuntimeException $exception) {
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function declineApplication(ClanJoinRequest $application): RedirectResponse
    {
        try {
            $this->clanApplicationService->decline(Auth::user(), $application);
            session()->flash('message', __('clan.application.declined'));
        } catch (RuntimeException $exception) {
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function withdrawApplication(ClanJoinRequest $application): RedirectResponse
    {
        try {
            $this->clanApplicationService->withdraw(Auth::user(), $application);
            session()->flash('message', __('clan.application.withdrawn'));
        } catch (RuntimeException $exception) {
            session()->flash('message', $exception->getMessage());
        }

        return redirect()->route('clan.rating');
    }

    public function member(): View|RedirectResponse
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

    public function role(): View|RedirectResponse
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

        if ((int) $user->location_id !== 29) {
            session()->flash('message', __('clan.directory.location_required'));

            return redirect()->route('clan.rating');
        }

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
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function invite(Request $request): RedirectResponse
    {
        try {
            $this->inviteToClan->execute(Auth::user(), $request->input('invite_nick'));
            session()->flash('message', 'Приглашение в клан отправлено');
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    /** Принятие приглашения из чата (переход по ссылке из iframe чата). */
    public function acceptInvite(Request $request, string $inviteUuid): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $this->acceptClanInvite->execute($user, $inviteUuid);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Вы вступили в клан.']);
            }

            return redirect()->route('game')->with('clan_success', 'Вы вступили в клан.');
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->route('game')->with('clan_error', $e->getMessage());
        }
    }

    /** Отклонение приглашения из чата. */
    public function declineInvite(Request $request, string $inviteUuid): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $this->declineClanInvite->execute($user, $inviteUuid);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Приглашение отклонено.']);
            }

            return redirect()->route('game')->with('clan_success', 'Приглашение отклонено.');
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->route('game')->with('clan_error', $e->getMessage());
        }
    }

    public function leaveClan(): RedirectResponse
    {
        try {
            $this->leaveClan->execute(Auth::user());
            session()->flash('message', 'Вы покинули клан.');
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan');
    }

    public function kickMember(User $target): RedirectResponse
    {
        try {
            $this->kickClanMember->execute(Auth::user(), $target);
            session()->flash('message', 'Игрок исключён из клана.');
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function saveMemberRoles(Request $request): RedirectResponse
    {
        try {
            $this->saveClanMemberRoles->execute(Auth::user(), $request->input('form.mem', []));
            session()->flash('message', 'Звания участников сохранены.');
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function addRole(Request $request): RedirectResponse
    {
        try {
            $this->addClanRole->execute(Auth::user(), $request->input('name'));
            session()->flash('message', 'Звание добавлено.');
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function deleteRole(ClanRole $role): RedirectResponse
    {
        try {
            $this->deleteClanRole->execute(Auth::user(), $role);
            session()->flash('message', 'Звание удалено.');
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function saveRoles(Request $request): RedirectResponse
    {
        try {
            $this->saveClanRoles->execute(Auth::user(), $request->input('form.grades', []));
            session()->flash('message', 'Полномочия сохранены.');
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function information(): View|RedirectResponse
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

    public function characteristics(): View|RedirectResponse
    {
        try {
            $page = $this->getClanCharacteristicsPage->execute(Auth::user());
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::characteristics', [
            'clan' => $page->clan,
            'membership' => $page->membership,
            'nextLevel' => $page->nextLevel,
            'currentLevelExperience' => $page->currentLevelExperience,
            'experienceToNextLevel' => $page->experienceToNextLevel,
            'progressPercent' => $page->progressPercent,
        ]);
    }

    public function saveDescription(Request $request): RedirectResponse
    {
        try {
            $this->saveClanDescription->execute(Auth::user(), $request->input('description', ''));
            session()->flash('message', 'Описание клана сохранено.');
        } catch (RuntimeException $e) {
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
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.information');
    }

    public function quests(Request $request): View|RedirectResponse
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

    public function boss(Request $request): View|RedirectResponse
    {
        try {
            $archiveRunId = $request->integer('run') ?: null;
            $page = $this->getClanBossPage->execute(Auth::user(), $archiveRunId);
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::boss', [
            'clan' => $page->clan,
            'membership' => $page->membership,
            'run' => $page->run,
            'currentState' => $page->currentState,
            'previousBosses' => $page->previousBosses,
            'nextBoss' => $page->nextBoss,
            'upcomingBosses' => $page->upcomingBosses,
            'damageRows' => $page->damageRows,
            'myContribution' => $page->myContribution,
            'hitsLeft' => $page->hitsLeft,
            'personalAvailableAt' => $page->personalAvailableAt,
            'archiveRuns' => $page->archiveRuns,
            'archivedRun' => $page->archivedRun,
            'archivedStages' => $page->archivedStages,
            'archivedDamageRows' => $page->archivedDamageRows,
            'archivedStageDamage' => $page->archivedStageDamage,
            'rewardReceipts' => $page->rewardReceipts,
            'archivedRewardReceipts' => $page->archivedRewardReceipts,
        ]);
    }

    public function logs(Request $request): View|RedirectResponse
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
