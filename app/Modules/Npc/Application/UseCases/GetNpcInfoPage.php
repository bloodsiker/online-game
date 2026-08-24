<?php

declare(strict_types=1);

namespace App\Modules\Npc\Application\UseCases;

use App\Modules\Npc\Application\DTOs\NpcInfoPageDTO;
use App\Modules\Npc\Domain\Contracts\NpcReadRepository;

class GetNpcInfoPage
{
    public function __construct(
        private readonly NpcReadRepository $readRepository,
    ) {}

    public function execute(string $uuid): NpcInfoPageDTO
    {
        return new NpcInfoPageDTO(
            $this->readRepository->findNpcByUuidOrFail($uuid),
        );
    }
}
