<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\Structure\Bank\Domain\Models\BankLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Transfer
{
    public const COMMISSION = 0.01; // 1%

    /**
     * @return array{ok: bool, message: string}
     */
    public function execute(User $sender, string $account, int $amount): array
    {
        $recipient = User::where('bank_account', $account)->first();

        if (! $recipient) {
            return ['ok' => false, 'message' => 'Счёт не найден.'];
        }

        if ($recipient->id === $sender->id) {
            return ['ok' => false, 'message' => 'Нельзя переводить самому себе.'];
        }

        $commission = (int) ceil($amount * self::COMMISSION);
        $total      = $amount + $commission;

        if ($sender->bank_balance < $total) {
            return ['ok' => false, 'message' => 'Недостаточно монет на счёте (с учётом комиссии).'];
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

        return ['ok' => true, 'message' => sprintf(
            'Перевод выполнен. Списано: %s (перевод: %s + комиссия: %s) монет.',
            number_format($total),
            number_format($amount),
            number_format($commission),
        )];
    }
}