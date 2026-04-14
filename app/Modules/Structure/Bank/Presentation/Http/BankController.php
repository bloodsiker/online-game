<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Presentation\Http;

use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Http\Controllers\Controller;
use App\Modules\Structure\Bank\Domain\Models\BankLog;
use App\Models\User;
use App\Modules\Structure\Bank\Application\UseCases\Deposit;
use App\Modules\Structure\Bank\Application\UseCases\Transfer;
use App\Modules\Structure\Bank\Application\UseCases\Withdraw;
use App\Modules\Structure\Bank\Domain\Services\BankAccountGenerator;
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
        private readonly BankAccountGenerator $accountGenerator,
    ) {}

    public function index(Request $request): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->bank_account) {
            $user->bank_account = $this->accountGenerator->generate($user);
            $user->save();
        }

        if ($request->isMethod('POST')) {
            $action = $request->input('action');
            $amount = (int) $request->input('amount', 0);

            if ($amount <= 0) {
                session()->flash('message', 'Укажите корректную сумму.');
                return redirect()->back();
            }

            if ($action === BankAction::DEPOSIT->value) {
                if ($amount > $user->money) {
                    session()->flash('message', 'Сумма превышает количество монет в кошельке.');
                    return redirect()->back();
                }
                session()->flash('message', $this->deposit->execute($user, $amount));
                return redirect()->back();
            }

            if ($action === BankAction::WITHDRAW->value) {
                if ($amount > $user->bank_balance) {
                    session()->flash('message', 'Сумма превышает баланс банковского счёта.');
                    return redirect()->back();
                }
                session()->flash('message', $this->withdraw->execute($user, $amount));
                return redirect()->back();
            }

            if ($action === BankAction::TRANSFER_OUT->value) {
                $result = $this->transfer->execute($user, $request->input('account', ''), $amount);
                session()->flash('message', $result['message']);
                return redirect()->back();
            }
        }

        $logs = BankLog::with('relatedUser')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30);

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