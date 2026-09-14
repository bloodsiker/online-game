<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Domain\Contracts;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangeResultDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;

/**
 * Механика конкретной страницы обмена на репутацию. Каждая реализация — один
 * способ конвертировать предметы в очки репутации (детерминированный обмен,
 * обмен с шансом успеха и т.д.). Новая механика = новый класс + новый case в
 * ReputationExchangeType, без правок существующих реализаций.
 */
interface ReputationExchangeStrategy
{
    /**
     * @return array{page: object} Данные для блейд-шаблона, ключ 'page' — DTO страницы конкретной механики.
     */
    public function getPageData(User $user, Structure $structure): array;

    /**
     * @param  array<string, mixed>  $input  Сырые данные из формы (share_item_id, count, option_id и т.п. — свои для каждой механики).
     */
    public function perform(User $user, Structure $structure, array $input): ReputationExchangeResultDTO;

    /** Имя блейд-вью, которым рендерится страница этой механики. */
    public function viewName(): string;
}
