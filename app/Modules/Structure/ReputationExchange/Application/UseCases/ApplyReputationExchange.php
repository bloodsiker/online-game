<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Application\UseCases;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangeResultDTO;
use App\Modules\Structure\ReputationExchange\Domain\Services\ReputationExchangeService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;

class ApplyReputationExchange
{
    public function __construct(
        private readonly ReputationExchangeService $exchangeService,
    ) {}

    public function execute(User $user, int $structureId, int $shareItemId, int $count): ReputationExchangeResultDTO
    {
        $structure = Structure::with('npc')->findOrFail($structureId);

        if ($user->location_id !== $structure->npc->location_id) {
            return new ReputationExchangeResultDTO(false, 'Вы находитесь не в том месте для обмена.');
        }

        try {
            $this->exchangeService->performExchange($user, $structureId, $shareItemId, $count);
        } catch (DomainException $e) {
            return new ReputationExchangeResultDTO(false, $e->getMessage());
        }

        return new ReputationExchangeResultDTO(true, 'Реликт принят, репутация повышена!');
    }
}
