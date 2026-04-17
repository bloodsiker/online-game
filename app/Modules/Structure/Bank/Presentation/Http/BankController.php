<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Structure\Bank\Application\UseCases\Deposit;
use App\Modules\Structure\Bank\Application\UseCases\EnsureBankAccount;
use App\Modules\Structure\Bank\Application\UseCases\GetBankLogs;
use App\Modules\Structure\Bank\Application\UseCases\Transfer;
use App\Modules\Structure\Bank\Application\UseCases\Withdraw;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    public function __construct(
        private readonly Deposit $deposit,
        private readonly Withdraw $withdraw,
        private readonly Transfer $transfer,
        private readonly EnsureBankAccount $ensureBankAccount,
        private readonly GetBankLogs $getBankLogs,
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
                BankAction::DEPOSIT->value      => $this->deposit->execute($user, $amount),
                BankAction::WITHDRAW->value     => $this->withdraw->execute($user, $amount),
                BankAction::TRANSFER_OUT->value => $this->transfer->execute($user, $request->input('account', ''), $amount),
                default                         => null,
            };

            if ($result !== null) {
                session()->flash('message', $result->message);
                return redirect()->back();
            }
        }

        $logs = $this->getBankLogs->execute($user->id);

        return view('bank::index', compact('user', 'logs'));
    }

    public function lookup(Request $request): JsonResponse
    {
        $account   = $request->input('account', '');
        $recipient = User::where('bank_account', $account)->first();

        if (! $recipient) {
            return response()->json(['error' => 'Счёт не найден'], 404);
        }

        /** @var User $sender */
        $sender = Auth::user();

        if ($recipient->id === $sender->id) {
            return response()->json(['error' => 'Нельзя переводить самому себе'], 422);
        }

        return response()->json(['name' => $recipient->name]);
    }
}