<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BankAction;
use App\Models\BankLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public const TRANSFER_COMMISSION = 0.01; // 1%

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->bank_account) {
            $user->bank_account = $this->generateAccount($user);
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
                return $this->deposit($user, $amount);
            }

            if ($action === BankAction::WITHDRAW->value) {
                if ($amount > $user->bank_balance) {
                    session()->flash('message', 'Сумма превышает баланс банковского счёта.');
                    return redirect()->back();
                }
                return $this->withdraw($user, $amount);
            }

            if ($action === BankAction::TRANSFER_OUT->value) {
                return $this->transfer($user, $request->input('account', ''), $amount);
            }
        }

        $logs = BankLog::with('relatedUser')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('bank.index', compact('user', 'logs'));
    }

    private function deposit(User $user, int $amount): RedirectResponse
    {
        DB::transaction(function () use ($user, $amount) {
            $user->decrement('money', $amount);
            $user->increment('bank_balance', $amount);

            BankLog::create([
                'user_id'       => $user->id,
                'action'        => BankAction::DEPOSIT,
                'amount'        => $amount,
                'balance_after' => $user->fresh()->bank_balance,
            ]);
        });

        session()->flash('message', sprintf('Вы положили %s монет в банк.', number_format($amount)));
        return redirect()->back();
    }

    private function withdraw(User $user, int $amount): RedirectResponse
    {
        DB::transaction(function () use ($user, $amount) {
            $user->decrement('bank_balance', $amount);
            $user->increment('money', $amount);

            BankLog::create([
                'user_id'       => $user->id,
                'action'        => BankAction::WITHDRAW,
                'amount'        => $amount,
                'balance_after' => $user->fresh()->bank_balance,
            ]);
        });

        session()->flash('message', sprintf('Вы сняли %s монет из банка.', number_format($amount)));
        return redirect()->back();
    }

    private function transfer(User $sender, string $account, int $amount): RedirectResponse
    {
        $recipient = User::where('bank_account', $account)->first();

        if (! $recipient) {
            session()->flash('message', 'Счёт не найден.');
            return redirect()->back();
        }

        if ($recipient->id === $sender->id) {
            session()->flash('message', 'Нельзя переводить самому себе.');
            return redirect()->back();
        }

        $commission = (int) ceil($amount * self::TRANSFER_COMMISSION);
        $total      = $amount + $commission;

        if ($sender->bank_balance < $total) {
            session()->flash('message', 'Недостаточно монет на счёте (с учётом комиссии).');
            return redirect()->back();
        }

        DB::transaction(function () use ($sender, $recipient, $amount, $commission, $total) {
            $sender->decrement('bank_balance', $total);
            $recipient->increment('bank_balance', $amount);

            $senderBalance    = $sender->fresh()->bank_balance;
            $recipientBalance = $recipient->fresh()->bank_balance;

            BankLog::create([
                'user_id'         => $sender->id,
                'related_user_id' => $recipient->id,
                'action'          => BankAction::TRANSFER_OUT,
                'amount'          => $total,
                'balance_after'   => $senderBalance,
            ]);

            BankLog::create([
                'user_id'         => $recipient->id,
                'related_user_id' => $sender->id,
                'action'          => BankAction::TRANSFER_IN,
                'amount'          => $amount,
                'balance_after'   => $recipientBalance,
            ]);
        });

        session()->flash('message', sprintf(
            'Перевод выполнен. Списано: %s (перевод: %s + комиссия: %s) монет.',
            number_format($total),
            number_format($amount),
            number_format($commission),
        ));
        return redirect()->back();
    }

    public function lookup(Request $request): JsonResponse
    {
        $account = $request->input('account', '');
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

    private function generateAccount(User $user): string
    {
        return str_pad((string) (10000000 + $user->id), 8, '0', STR_PAD_LEFT);
    }
}