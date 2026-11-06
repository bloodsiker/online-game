<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Contracts\TransactionManager;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\ClanTreasuryLog;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class DepositClanTreasury
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
            if ((int) $user->money < $amount) {
                throw new RuntimeException('Сумма превышает количество монет в кошельке.');
            }
            if ((int) $context->clan->treasury > PHP_INT_MAX - $amount) {
                throw new RuntimeException('Превышен допустимый баланс казны.');
            }
            if (User::query()->whereKey($user->id)->where('money', '>=', $amount)->decrement('money', $amount) !== 1) {
                throw new RuntimeException('Сумма превышает количество монет в кошельке.');
            }
            $context->clan->increment('treasury', $amount);

            $balance = (int) $context->clan->treasury;

            ClanTreasuryLog::create([
                'clan_id' => $context->clan->id,
                'structure_id' => $clanWarehouse->id,
                'user_id' => $user->id,
                'action' => 'deposit',
                'amount' => $amount,
                'balance_after' => $balance,
            ]);

            $this->clanLogService->write(
                $context->clan,
                $user,
                ClanLogAction::TREASURY_DEPOSIT,
                (string) $amount,
            );
        });

        return sprintf('Вы внесли %s монет в казну клана.', number_format($amount));
    }
}
