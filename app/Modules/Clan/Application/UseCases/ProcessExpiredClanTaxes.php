<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Contracts\TransactionManager;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use Illuminate\Support\Carbon;

class ProcessExpiredClanTaxes
{
    public function __construct(
        private readonly TransactionManager $transactionManager,
        private readonly ClanSkillService $clanSkillService,
    ) {}

    public function execute(Carbon $now): int
    {
        $processed = 0;

        Clan::query()
            ->whereNotNull('tax_paid_until')
            ->where('tax_paid_until', '<=', $now)
            ->whereNull('tax_penalties_applied_at')
            ->select('id')
            ->chunkById(100, function ($clans) use ($now, &$processed): void {
                foreach ($clans as $candidate) {
                    $wasProcessed = $this->transactionManager->run(function () use ($candidate, $now): bool {
                        $clan = Clan::query()->lockForUpdate()->find($candidate->id);

                        if ($clan === null
                            || $clan->tax_penalties_applied_at !== null
                            || $clan->tax_paid_until === null
                            || $clan->tax_paid_until->isAfter($now)) {
                            return false;
                        }

                        $this->clanSkillService->removeAllSkillsFromClanMembers($clan);
                        $clan->forceFill(['tax_penalties_applied_at' => $now])->save();

                        return true;
                    });

                    if ($wasProcessed) {
                        $processed++;
                    }
                }
            });

        return $processed;
    }
}
