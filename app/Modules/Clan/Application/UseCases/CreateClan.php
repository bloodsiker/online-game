<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CreateClan
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user, string $name, UploadedFile $logo): void
    {
        if ((int) $user->location_id !== 29) {
            throw new RuntimeException(__('clan.directory.location_required'));
        }

        if ($user->clanMembership !== null) {
            throw new RuntimeException('Вы уже состоите в клане.');
        }

        $this->clanService->create($user, $name, $logo);
    }
}
