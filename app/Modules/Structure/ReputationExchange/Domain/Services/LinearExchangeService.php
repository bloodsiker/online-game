<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Domain\Services;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangeResultDTO;
use App\Modules\Structure\ReputationExchange\Application\UseCases\GetReputationExchangePage;
use App\Modules\Structure\ReputationExchange\Domain\Contracts\ReputationExchangeStrategy;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;

/**
 * Детерминированный обмен «предмет → фиксированные очки репутации»
 * (Искатели реликтов, Алое Братство). Логика самого обмена не менялась —
 * этот класс просто оборачивает GetReputationExchangePage/ReputationExchangeService
 * под общий интерфейс стратегий.
 */
readonly class LinearExchangeService implements ReputationExchangeStrategy
{
    public function __construct(
        private GetReputationExchangePage $getPage,
        private ReputationExchangeService $exchangeService,
    ) {}

    public function getPageData(User $user, Structure $structure): array
    {
        return ['page' => $this->getPage->execute($user, $structure->id)];
    }

    public function perform(User $user, Structure $structure, array $input): ReputationExchangeResultDTO
    {
        $shareItemId = (int) ($input['share_item_id'] ?? 0);
        $count = (int) ($input['count'] ?? 1);

        try {
            $this->exchangeService->performExchange($user, $structure->id, $shareItemId, $count);
        } catch (DomainException $e) {
            return new ReputationExchangeResultDTO(false, $e->getMessage());
        }

        return new ReputationExchangeResultDTO(true, 'Предмет принят, репутация повышена!');
    }

    public function viewName(): string
    {
        return 'reputation_exchange::index';
    }
}
