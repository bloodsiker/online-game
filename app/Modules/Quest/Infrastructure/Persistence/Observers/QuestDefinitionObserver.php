<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Observers;

use App\Modules\Quest\Domain\Services\QuestDefinitionsCache;

/**
 * Любое изменение статичного контента квестов (моделью или сидером)
 * сбрасывает версионный кэш определений.
 */
class QuestDefinitionObserver
{
    public function saved(): void
    {
        QuestDefinitionsCache::flush();
    }

    public function deleted(): void
    {
        QuestDefinitionsCache::flush();
    }
}
