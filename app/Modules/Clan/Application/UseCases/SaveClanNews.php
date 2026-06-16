<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class SaveClanNews
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user, string $news1, string $news2, string $news3): void
    {
        $this->clanService->saveNews($user, $news1, $news2, $news3);
    }
}
