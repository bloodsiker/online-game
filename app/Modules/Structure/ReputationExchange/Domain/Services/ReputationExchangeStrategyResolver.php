<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Domain\Services;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Domain\Contracts\ReputationExchangeStrategy;
use App\Modules\Structure\ReputationExchange\Domain\Enums\ReputationExchangeType;

/**
 * Единственное место, где механика обмена выбирается по данным структуры
 * (exchange_type). Новая механика — новый case в ReputationExchangeType и
 * новая ветка здесь, существующие стратегии не трогаются.
 */
readonly class ReputationExchangeStrategyResolver
{
    public function __construct(
        private LinearExchangeService $linear,
        private GambleExchangeService $gamble,
    ) {}

    public function resolve(Structure $structure): ReputationExchangeStrategy
    {
        $type = ReputationExchangeType::tryFrom((string) $structure->exchange_type) ?? ReputationExchangeType::LINEAR;

        return match ($type) {
            ReputationExchangeType::LINEAR => $this->linear,
            ReputationExchangeType::GAMBLE => $this->gamble,
        };
    }
}
