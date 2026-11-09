<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\UseCases;

use App\Modules\Battle\Domain\Contracts\HomepageBossBattlesReader;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetHomepageBossBattles
{
    public function __construct(private HomepageBossBattlesReader $battles) {}

    /** @return Collection<int, Battle> */
    public function execute(): Collection
    {
        return $this->battles->latest(limit: 10);
    }
}
