<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanTreasuryPageDTO;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanTreasuryLog;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanTreasuryPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user, int $structureId): ClanTreasuryPageDTO
    {
        $context = $this->resolveClanContext->require($user);
        $clanWarehouse = Structure::findOrFail($structureId);

        if (! $clanWarehouse->isClanBank()) {
            abort(404);
        }

        $isLeader = (bool) $context->membership->role->is_leader;
        $canPayTax = $context->membership->role->hasPermission(ClanPermission::PAY_TAX);
        $taxPaid = $context->clan->hasPaidTax();

        if (! $taxPaid && ! $isLeader && ! $canPayTax) {
            throw new \RuntimeException('До оплаты налога клановый банк доступен только главе и участникам с правом оплаты налога.');
        }

        $logs = ClanTreasuryLog::with('user')
            ->where('clan_id', $context->clan->id)
            ->where('structure_id', $clanWarehouse->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return new ClanTreasuryPageDTO(
            clanWarehouse: $clanWarehouse,
            clan: $context->clan,
            membership: $context->membership,
            canWithdraw: $context->membership->role->hasPermission(ClanPermission::WITHDRAW_MONEY),
            isLeader: $isLeader,
            canPayTax: $canPayTax,
            taxPaid: $taxPaid,
            taxAmount: max(1, (int) config('game.clan_monthly_tax', 1000000)),
            logs: $logs,
        );
    }
}
