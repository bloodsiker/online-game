<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ClanLogAction;
use App\Enums\ClanPermission;
use App\Models\Clan\ClanLog;
use App\Models\Clan\ClanTreasuryLog;
use App\Models\Structure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClanTreasuryController extends Controller
{
    public function index(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user          = Auth::user();
        $clanWarehouse = Structure::findOrFail($id);

        if (! $clanWarehouse->isClanBank()) {
            abort(404);
        }

        $membership = $user->clanMembership;
        if ($membership === null) {
            session()->flash('message', 'Вы не состоите в клане.');
            return redirect()->route('clan');
        }

        $clan       = $membership->clan;
        $canWithdraw = $membership->role->hasPermission(ClanPermission::WITHDRAW_MONEY);

        if ($request->isMethod('POST')) {
            $action = $request->input('action');
            $amount = (int) $request->input('amount', 0);

            if ($amount <= 0) {
                session()->flash('message', 'Укажите корректную сумму.');
                return redirect()->back();
            }

            if ($action === 'deposit') {
                if ($amount > $user->money) {
                    session()->flash('message', 'Сумма превышает количество монет в кошельке.');
                    return redirect()->back();
                }
                return $this->deposit($user, $clan, $clanWarehouse, $amount);
            }

            if ($action === 'withdraw' && $canWithdraw) {
                if ($amount > $clan->treasury) {
                    session()->flash('message', 'Сумма превышает баланс казны.');
                    return redirect()->back();
                }
                return $this->withdraw($user, $clan, $clanWarehouse, $amount);
            }
        }

        $logs = ClanTreasuryLog::with('user')
            ->where('clan_id', $clan->id)
            ->where('structure_id', $clanWarehouse->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('clan.treasury.index', compact('clanWarehouse', 'clan', 'membership', 'canWithdraw', 'logs'));
    }

    private function deposit(\App\Models\User $user, $clan, $clanWarehouse, int $amount): RedirectResponse
    {
        if ($user->money < $amount) {
            session()->flash('message', 'Недостаточно монет.');
            return redirect()->back();
        }

        DB::transaction(function () use ($user, $clan, $clanWarehouse, $amount) {
            $user->decrement('money', $amount);
            $clan->increment('treasury', $amount);

            $balance = $clan->fresh()->treasury;

            ClanTreasuryLog::create([
                'clan_id'      => $clan->id,
                'structure_id' => $clanWarehouse->id,
                'user_id'      => $user->id,
                'action'       => 'deposit',
                'amount'       => $amount,
                'balance_after' => $balance,
            ]);

            ClanLog::create([
                'clan_id' => $clan->id,
                'user_id' => $user->id,
                'action'  => ClanLogAction::TREASURY_DEPOSIT,
                'details' => (string) $amount,
            ]);
        });

        session()->flash('message', sprintf('Вы внесли %s монет в казну клана.', number_format($amount)));
        return redirect()->back();
    }

    private function withdraw(\App\Models\User $user, $clan, $clanWarehouse, int $amount): RedirectResponse
    {
        if ($clan->treasury < $amount) {
            session()->flash('message', 'В казне недостаточно монет.');
            return redirect()->back();
        }

        DB::transaction(function () use ($user, $clan, $clanWarehouse, $amount) {
            $clan->decrement('treasury', $amount);
            $user->increment('money', $amount);

            $balance = $clan->fresh()->treasury;

            ClanTreasuryLog::create([
                'clan_id'       => $clan->id,
                'structure_id'  => $clanWarehouse->id,
                'user_id'       => $user->id,
                'action'        => 'withdraw',
                'amount'        => $amount,
                'balance_after' => $balance,
            ]);

            ClanLog::create([
                'clan_id' => $clan->id,
                'user_id' => $user->id,
                'action'  => ClanLogAction::TREASURY_WITHDRAW,
                'details' => (string) $amount,
            ]);
        });

        session()->flash('message', sprintf('Вы сняли %s монет из казны клана.', number_format($amount)));
        return redirect()->back();
    }
}
