<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Contracts;

use App\Models\Share\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface BlacksmithReadRepository
{
    public function findStructureOrFail(int $id): Structure;

    public function findCrystalOrFail(): ShareItem;

    /** @return Collection<int, mixed> */
    public function getCraftRecipes(User $user): Collection;

    /** @return array<int, array{id:int, count:int}> */
    public function getResourceCounts(User $user): array;

    /** @return Collection<int, mixed> */
    public function getBreakableItems(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getUpgradeableItems(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getBaseScrolls(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getBonusScrolls(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getSocketableItems(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getGems(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getSocketKits(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getImbueableItems(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getRunes(User $user): Collection;

    /** @return Collection<int, mixed> */
    public function getRuneKeys(User $user): Collection;
}
