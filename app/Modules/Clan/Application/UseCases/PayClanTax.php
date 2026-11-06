<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanTaxPayment;
use App\Modules\Clan\Domain\Services\ClanActionTransaction;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use RuntimeException;

class PayClanTax
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
        private readonly ClanActionTransaction $actions,
        private readonly ClanSkillService $clanSkillService,
        private readonly ClanLogService $clanLogService,
    ) {}

    public function execute(User $user, int $structureId): string
    {
        $context = $this->resolveClanContext->require($user);

        if (! $context->membership->role->hasPermission(ClanPermission::PAY_TAX)) {
            throw new RuntimeException('У вас нет права оплачивать налог клана.');
        }

        $clanBank = Structure::findOrFail($structureId);
        if (! $clanBank->isClanBank()) {
            abort(404);
        }

        $amount = max(1, (int) config('game.clan_monthly_tax', 1000000));

        /** @var Clan $clan */
        $clan = $this->actions->run(clanId: (int) $context->clan->id, userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($user, $clanBank, $amount): Clan {
            $user = $users->get($user->id);
            if ($user->clanMembership === null || (int) $user->clanMembership->clan_id !== (int) $clan->id
                || ! $user->clanMembership->role?->hasPermission(ClanPermission::PAY_TAX)) {
                throw new RuntimeException('У вас нет права оплачивать налог клана.');
            }

            if ($clan->treasury < $amount) {
                throw new RuntimeException('В казне клана недостаточно монет для оплаты налога.');
            }

            $now = now();
            $paidFrom = $clan->tax_paid_until !== null && $clan->tax_paid_until->isFuture()
                ? $clan->tax_paid_until->copy()
                : $now->copy();
            $paidUntil = $paidFrom->copy()->addMonthNoOverflow();
            $balanceBefore = (int) $clan->treasury;

            $clan->forceFill([
                'treasury' => $balanceBefore - $amount,
                'tax_paid_until' => $paidUntil,
                'tax_penalties_applied_at' => null,
            ])->save();

            ClanTaxPayment::create([
                'clan_id' => $clan->id,
                'user_id' => $user->id,
                'structure_id' => $clanBank->id,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => (int) $clan->treasury,
                'paid_from' => $paidFrom,
                'paid_until' => $paidUntil,
            ]);

            $this->clanLogService->write(
                $clan,
                $user,
                ClanLogAction::TAX_PAID,
                sprintf(
                    '%s оплатил налог: %s монет до %s.',
                    $user->name,
                    number_format($amount, 0, '', ' '),
                    $paidUntil->format('d.m.Y H:i'),
                ),
            );

            Player::whereIn('id', $users->pluck('player_id')->filter())->orderBy('id')->lockForUpdate()->get();
            $this->clanSkillService->applyAllSkillsToClanMembers($clan);

            return $clan;
        }, allMembers: true);

        return sprintf(
            'Налог оплачен: %s монет. Действует до %s.',
            number_format($amount, 0, '', ' '),
            $clan->tax_paid_until->format('d.m.Y H:i'),
        );
    }
}
