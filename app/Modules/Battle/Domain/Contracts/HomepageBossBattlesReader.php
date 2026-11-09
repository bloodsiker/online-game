<?php

declare(strict_types=1);

namespace App\Modules\Battle\Domain\Contracts;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use Illuminate\Database\Eloquent\Collection;

interface HomepageBossBattlesReader
{
    /** @return Collection<int, Battle> */
    public function latest(int $limit): Collection;
}
