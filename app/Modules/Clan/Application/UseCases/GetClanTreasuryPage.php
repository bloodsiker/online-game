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
            logs: $logs,
        );
    }
}
