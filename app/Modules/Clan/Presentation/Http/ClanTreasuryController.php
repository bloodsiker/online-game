<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Application\UseCases\DepositClanTreasury;
use App\Modules\Clan\Application\UseCases\GetClanTreasuryPage;
use App\Modules\Clan\Application\UseCases\WithdrawClanTreasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ClanTreasuryController extends Controller
{
    public function __construct(
        private readonly GetClanTreasuryPage $getClanTreasuryPage,
        private readonly DepositClanTreasury $depositClanTreasury,
        private readonly WithdrawClanTreasury $withdrawClanTreasury,
    ) {}

    public function index(Request $request, int $id): mixed
    {
        if ($request->isMethod('POST')) {
            try {
                $message = match ($request->input('action')) {
                    'deposit' => $this->depositClanTreasury->execute(Auth::user(), $id, (int) $request->input('amount', 0)),
                    'withdraw' => $this->withdrawClanTreasury->execute(Auth::user(), $id, (int) $request->input('amount', 0)),
                    default => throw new RuntimeException('Неизвестное действие.'),
                };

                session()->flash('message', $message);
            } catch (RuntimeException $e) {
                session()->flash('message', $e->getMessage());
            }

            return redirect()->back();
        }

        try {
            $page = $this->getClanTreasuryPage->execute(Auth::user(), $id);
        } catch (RuntimeException $e) {
            session()->flash('message', $e->getMessage());

            return redirect()->route('clan');
        }

        return view('clan::treasury.index', [
            'clanWarehouse' => $page->clanWarehouse,
            'clan' => $page->clan,
            'membership' => $page->membership,
            'canWithdraw' => $page->canWithdraw,
            'logs' => $page->logs,
        ]);
    }
}
