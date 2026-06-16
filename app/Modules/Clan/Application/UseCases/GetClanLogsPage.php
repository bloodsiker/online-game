<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanLogsPageDTO;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetClanLogsPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
    ) {}

    public function execute(User $user, ?string $filterAction, ?string $filterUser): ClanLogsPageDTO
    {
        $context = $this->resolveClanContext->require($user);

        $query = ClanLog::where('clan_id', $context->membership->clan_id)
            ->with('user')
            ->orderByDesc('id');

        if ($filterAction && ClanLogAction::tryFrom($filterAction)) {
            $query->where('action', $filterAction);
        }

        if ($filterUser) {
            $query->whereHas('user', fn ($builder) => $builder->where('name', 'like', '%'.$filterUser.'%'));
        }

        return new ClanLogsPageDTO(
            logs: $query->paginate(50)->withQueryString(),
            actions: ClanLogAction::cases(),
            membership: $context->membership,
            filterAction: $filterAction,
            filterUser: $filterUser,
        );
    }
}
