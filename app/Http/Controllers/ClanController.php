<?php

namespace App\Http\Controllers;

use App\Enums\ClanPermission;
use App\Http\Requests\Clan\CreateClanRequest;
use App\Models\Clan\ClanJoinRequest;
use App\Models\Clan\ClanRole;
use App\Models\User;
use App\Services\ClanService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClanController extends Controller
{
    public function __construct(private readonly ClanService $clanService) {}

    public function index()
    {
        $inClan = Auth::user()->clanMembership !== null;

        return view('clan.index', compact('inClan'));
    }

    public function member()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $membership = $user->clanMembership;

        if ($membership === null) {
            session()->flash('message', 'Вы не состоите в клане.');

            return redirect()->route('clan');
        }

        $clan = $membership->clan()->with(['members.user.currentLocation', 'members.role', 'roles'])->first();

        $onlineThreshold = Carbon::now()->subMinutes(10);

        $memberRows = $clan->members->map(fn ($m) => [
            'type'     => 'member',
            'user'     => $m->user,
            'role'     => $m->role,
            'is_online' => $m->user->last_online_at && $m->user->last_online_at > $onlineThreshold,
        ]);

        $requestRows = ClanJoinRequest::where('clan_id', $clan->id)
            ->with('user')
            ->get()
            ->map(fn ($r) => [
                'type'      => 'request',
                'id'        => $r->id,
                'user'      => $r->user,
                'status'    => $r->status,
                'is_online' => false,
            ]);

        $rows       = $memberRows->concat($requestRows);
        $allRoles   = $clan->roles;
        $leaderRole = $clan->roles->firstWhere('is_leader', true);

        $onlineCount = $memberRows->filter(fn ($r) => $r['is_online'])->count();
        $canKick     = $membership->role->hasPermission(ClanPermission::KICK);
        $canInvite   = $membership->role->hasPermission(ClanPermission::INVITE);

        return view('clan.member', compact('clan', 'rows', 'membership', 'allRoles', 'leaderRole', 'onlineCount', 'canKick', 'canInvite'));
    }

    public function role()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $membership = $user->clanMembership;

        if ($membership === null) {
            session()->flash('message', 'Вы не состоите в клане.');

            return redirect()->route('clan');
        }

        $clan           = $membership->clan()->with('roles')->first();
        $roles          = $clan->roles;
        $permissions    = ClanPermission::cases();
        $canChangePerms = $membership->role->hasPermission(ClanPermission::CHANGE_PERMS);

        return view('clan.role', compact('clan', 'roles', 'membership', 'permissions', 'canChangePerms'));
    }

    public function store(CreateClanRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->clanMembership !== null) {
            session()->flash('message', 'Вы уже состоите в клане.');
            return redirect()->route('clan');
        }

        $this->clanService->create($user, $request->input('name'), $request->file('logo'));

        session()->flash('message', 'Клан успешно создан!');
        return redirect()->route('clan.member');
    }

    public function cancelRequest(ClanJoinRequest $joinRequest): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $membership = $user->clanMembership;

        if ($membership === null || $membership->clan_id !== $joinRequest->clan_id) {
            session()->flash('message', 'Вы не можете отменить заявку');
            return redirect()->back();
        }

        try {
            $this->clanService->inviteCancel($user, $joinRequest);
            session()->flash('message', 'Заявка отменена.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function invite(Request $request): RedirectResponse
    {
        if (!$request->filled('invite_nick')) {
            session()->flash('message', 'Введите ник игрока.');
            return redirect()->back();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->invite($user, $request->input('invite_nick'));
            session()->flash('message', 'Приглашение в клан отправлено');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function leaveClan(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->leaveClan($user);
            session()->flash('message', 'Вы покинули клан.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan');
    }

    public function kickMember(User $target): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->kickMember($user, $target);
            session()->flash('message', 'Игрок исключён из клана.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function saveMemberRoles(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->saveMemberRoles($user, $request->input('form.mem', []));
            session()->flash('message', 'Звания участников сохранены.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.member');
    }

    public function addRole(Request $request): RedirectResponse
    {
        if (!$request->filled('name')) {
            session()->flash('message', 'Введите название звания.');
            return redirect()->back();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->addRole($user, $request->input('name'));
            session()->flash('message', 'Звание добавлено.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function deleteRole(ClanRole $role): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->deleteRole($user, $role);
            session()->flash('message', 'Звание удалено.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function saveRoles(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->saveRoles($user, $request->input('form.grades', []));
            session()->flash('message', 'Полномочия сохранены.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.role');
    }

    public function information()
    {
        /** @var \App\Models\User $user */
        $user       = Auth::user();
        $membership = $user->clanMembership;

        if ($membership === null) {
            session()->flash('message', 'Вы не состоите в клане.');
            return redirect()->route('clan');
        }

        $clan           = $membership->clan;
        $canChangeNews  = $membership->role->hasPermission(ClanPermission::CHANGE_NEWS);

        return view('clan.information', compact('clan', 'membership', 'canChangeNews'));
    }

    public function saveDescription(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->saveDescription($user, $request->input('description', ''));
            session()->flash('message', 'Описание клана сохранено.');
        } catch (\RuntimeException $e) {
            session()->flash('message', $e->getMessage());
        }

        return redirect()->route('clan.information');
    }

    public function saveNews(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $this->clanService->saveNews(
                $user,
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
}
