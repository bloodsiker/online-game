<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\UseCases;

use App\Models\Structure;
use App\Modules\Structure\Exchange\Application\DTOs\ExchangeActionDTO;
use App\Modules\Structure\Exchange\Application\DTOs\ExchangeResultDTO;
use App\Modules\Structure\Exchange\Domain\Services\ExchangeItemService;
use DomainException;

class ApplyExchange
{
    public function __construct(
        private readonly ExchangeItemService $exchangeItemService,
    ) {}

    public function execute(ExchangeActionDTO $data): ExchangeResultDTO
    {
        $exchange = Structure::with('npc')->findOrFail($data->exchangeId);

        if ($data->user->location_id !== $exchange->npc->location_id) {
            return new ExchangeResultDTO(false, 'Вы находитесь не в том месте для обмена.');
        }

        try {
            $this->exchangeItemService->performExchange(
                user: $data->user,
                exchange: $exchange,
                fromShareId: $data->fromShareId,
                toShareId: $data->toShareId,
                count: $data->count,
            );
        } catch (DomainException $e) {
            return new ExchangeResultDTO(false, $e->getMessage());
        }

        return new ExchangeResultDTO(true, 'Обмен успешно выполнен!');
    }
}
