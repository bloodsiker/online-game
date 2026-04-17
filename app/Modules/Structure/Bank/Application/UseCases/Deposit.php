<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\Structure\Bank\Domain\Models\BankLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Deposit
{
    public function execute(User $user, int $amount): BankResultDTO
    {
        if ($amount > $user->money) {
            return new BankResultDTO(false, 'Сумма превышает количество монет в кошельке.');
        }

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

        return new BankResultDTO(true, sprintf('Вы положили %s монет в банк.', number_format($amount)));
    }
}