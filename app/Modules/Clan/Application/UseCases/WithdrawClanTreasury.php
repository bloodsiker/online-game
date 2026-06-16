<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Contracts\TransactionManager;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\Clan\Domain\Models\ClanTreasuryLog;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class WithdrawClanTreasury
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $user, int $structureId, int $amount): string
    {
        if ($amount <= 0) {
            throw new RuntimeException('Укажите корректную сумму.');
        }

        $context = $this->resolveClanContext->requirePermission(
            $user,
            ClanPermission::WITHDRAW_MONEY,
            'У вас нет прав снимать деньги из казны клана.'
        );

        $clanWarehouse = Structure::findOrFail($structureId);

        if (! $clanWarehouse->isClanBank()) {
            abort(404);
        }

        if ($amount > $context->clan->treasury) {
            throw new RuntimeException('Сумма превышает баланс казны.');
        }

        $this->transactionManager->run(function () use ($user, $context, $clanWarehouse, $amount) {
            $context->clan->decrement('treasury', $amount);
            $user->increment('money', $amount);

            $balance = $context->clan->fresh()->treasury;

            ClanTreasuryLog::create([
                'clan_id' => $context->clan->id,
                'structure_id' => $clanWarehouse->id,
                'user_id' => $user->id,
                'action' => 'withdraw',
                'amount' => $amount,
                'balance_after' => $balance,
            ]);

            ClanLog::create([
                'clan_id' => $context->clan->id,
                'user_id' => $user->id,
                'action' => ClanLogAction::TREASURY_WITHDRAW,
                'details' => (string) $amount,
            ]);
        });

        return sprintf('Вы сняли %s монет из казны клана.', number_format($amount));
    }
}
