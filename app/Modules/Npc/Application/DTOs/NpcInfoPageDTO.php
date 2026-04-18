<?php

declare(strict_types=1);

namespace App\Modules\Npc\Application\DTOs;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;

final readonly class NpcInfoPageDTO
{
    public function __construct(
        public Npc $npc,
    ) {}
}
