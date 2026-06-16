<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CreateClan
{
    public function __construct(
        private readonly ClanService $clanService,
        private readonly ClanSkillService $clanSkillService,
    ) {}

    public function execute(User $user, string $name, UploadedFile $logo): void
    {
        if ($user->clanMembership !== null) {
            throw new RuntimeException('Вы уже состоите в клане.');
        }

        $clan = $this->clanService->create($user, $name, $logo);

        if ($user->player !== null) {
            $this->clanSkillService->applyAllSkillsToPlayer($user->player, $clan);
        }
    }
}
