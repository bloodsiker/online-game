<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Bank\Application\UseCases\ClaimDeposit;
use App\Modules\Structure\Bank\Application\UseCases\Deposit;
use App\Modules\Structure\Bank\Application\UseCases\EnsureBankAccount;
use App\Modules\Structure\Bank\Application\UseCases\GetBankPage;
use App\Modules\Structure\Bank\Application\UseCases\GetDepositsPage;
use App\Modules\Structure\Bank\Application\UseCases\LookupRecipient;
use App\Modules\Structure\Bank\Application\UseCases\OpenDeposit;
use App\Modules\Structure\Bank\Application\UseCases\Transfer;
use App\Modules\Structure\Bank\Application\UseCases\Withdraw;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    public function __construct(
        private readonly Deposit $deposit,
        private readonly Withdraw $withdraw,
        private readonly Transfer $transfer,
        private readonly EnsureBankAccount $ensureBankAccount,
        private readonly GetBankPage $getBankPage,
        private readonly LookupRecipient $lookupRecipient,
        private readonly OpenDeposit $openDeposit,
        private readonly ClaimDeposit $claimDeposit,
        private readonly GetDepositsPage $getDepositsPage,
    ) {}

    public function index(Request $request): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        $this->ensureBankAccount->execute($user);

        if ($request->isMethod('POST')) {
            $action = $request->input('action');
            $amount = (int) $request->input('amount', 0);

            if ($amount <= 0) {
                session()->flash('message', 'Укажите корректную сумму.');

                return redirect()->back();
            }

            $result = match ($action) {
                BankAction::DEPOSIT->value => $this->deposit->execute($user, $amount),
                BankAction::WITHDRAW->value => $this->withdraw->execute($user, $amount),
                BankAction::TRANSFER_OUT->value => $this->transfer->execute($user, $request->input('account', ''), $amount),
                default => null,
            };

            if ($result !== null) {
                session()->flash('message', $result->message);

                return redirect()->back();
            }
        }

        $page = $this->getBankPage->execute($user);

        return view('bank::index', [
            'user' => $user,
            'page' => $page,
        ]);
    }

    public function deposits(Request $request): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        if ($request->isMethod('POST')) {
            $result = match ($request->input('action')) {
                'open' => $this->openDeposit->execute($user, (int) $request->input('amount', 0), (int) $request->input('term', 0)),
                'claim' => $this->claimDeposit->execute($user, (int) $request->input('deposit_id', 0)),
                default => null,
            };

            if ($result !== null) {
                session()->flash('message', $result->message);
            }

            return redirect()->route('bank.deposits', ['id' => $request->input('id')]);
        }

        return view('bank::deposits', [
            'user' => $user,
            'page' => $this->getDepositsPage->execute($user),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        /** @var User $sender */
        $sender = Auth::user();
        $result = $this->lookupRecipient->execute($sender, $request->input('account', ''));

        if (! $result->ok) {
            return response()->json(['error' => $result->error], $result->status);
        }

        return response()->json(['name' => $result->name]);
    }
}
