<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Contracts\TransactionManager;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\Clan\Domain\Models\ClanTreasuryLog;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class DepositClanTreasury
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

        $context = $this->resolveClanContext->require($user);
        $clanWarehouse = Structure::findOrFail($structureId);

        if (! $clanWarehouse->isClanBank()) {
            abort(404);
        }

        if ($amount > $user->money) {
            throw new RuntimeException('Сумма превышает количество монет в кошельке.');
        }

        $this->transactionManager->run(function () use ($user, $context, $clanWarehouse, $amount) {
            $user->decrement('money', $amount);
            $context->clan->increment('treasury', $amount);

            $balance = $context->clan->fresh()->treasury;

            ClanTreasuryLog::create([
                'clan_id' => $context->clan->id,
                'structure_id' => $clanWarehouse->id,
                'user_id' => $user->id,
                'action' => 'deposit',
                'amount' => $amount,
                'balance_after' => $balance,
            ]);

            ClanLog::create([
                'clan_id' => $context->clan->id,
                'user_id' => $user->id,
                'action' => ClanLogAction::TREASURY_DEPOSIT,
                'details' => (string) $amount,
            ]);
        });

        return sprintf('Вы внесли %s монет в казну клана.', number_format($amount));
    }
}
