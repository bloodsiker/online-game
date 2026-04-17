<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\Structure\Bank\Domain\Models\BankLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Withdraw
{
    public function execute(User $user, int $amount): BankResultDTO
    {
        if ($amount > $user->bank_balance) {
            return new BankResultDTO(false, 'Сумма превышает баланс банковского счёта.');
        }

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

        return new BankResultDTO(true, sprintf('Вы сняли %s монет из банка.', number_format($amount)));
    }
}