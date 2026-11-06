<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Contracts\TransactionManager;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanTreasuryLog;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class WithdrawClanTreasury
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
        private readonly TransactionManager $transactionManager,
        private readonly ClanLogService $clanLogService,
    ) {}

    public function execute(User $user, int $structureId, int $amount): string
    {
        if ($amount <= 0) {
            throw new RuntimeException('Укажите корректную сумму.');
        }

        $context = $this->resolveClanContext->require($user);

        $clanWarehouse = Structure::findOrFail($structureId);
        if (! $clanWarehouse->isClanBank()) {
            abort(404);
        }

        $this->transactionManager->run(function () use ($user, $context, $clanWarehouse, $amount): void {
            $context = $this->resolveClanContext->lockForTreasury(user: $user, clanId: (int) $context->clan->id);
            $user = $context->user;
            if (! $context->clan->hasPaidTax() && ! $context->membership->role->is_leader) {
                throw new RuntimeException('До оплаты налога клановый банк доступен только главе.');
            }
            if (! $context->membership->role->hasPermission(ClanPermission::WITHDRAW_MONEY)) {
                throw new RuntimeException('У вас нет прав снимать деньги из казны клана.');
            }
            if ((int) $context->clan->treasury < $amount) {
                throw new RuntimeException('Сумма превышает баланс казны.');
            }
            if (! WalletBalanceLimits::canCredit((int) $user->money, $amount)) {
                throw new RuntimeException('Превышен допустимый баланс кошелька.');
            }
            if ($context->clan->newQuery()->whereKey($context->clan->id)->where('treasury', '>=', $amount)->decrement('treasury', $amount) !== 1) {
                throw new RuntimeException('Сумма превышает баланс казны.');
            }
            $user->increment('money', $amount);

            $balance = (int) $context->clan->treasury - $amount;

            ClanTreasuryLog::create([
                'clan_id' => $context->clan->id,
                'structure_id' => $clanWarehouse->id,
                'user_id' => $user->id,
                'action' => 'withdraw',
                'amount' => $amount,
                'balance_after' => $balance,
            ]);

            $this->clanLogService->write(
                $context->clan,
                $user,
                ClanLogAction::TREASURY_WITHDRAW,
                (string) $amount,
            );
        });

        return sprintf('Вы сняли %s монет из казны клана.', number_format($amount));
    }
}
