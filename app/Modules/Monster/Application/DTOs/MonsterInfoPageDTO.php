<?php

declare(strict_types=1);

namespace App\Modules\Monster\Application\DTOs;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;

final readonly class MonsterInfoPageDTO
{
    /**
     * @param  list<string>  $habitatMaps
     */
    public function __construct(
        public Monster $monster,
        public array $habitatMaps,
    ) {}
}
