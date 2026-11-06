<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\Repositories;

use App\Modules\Backpack\Domain\Enums\BackpackCategory;

interface BackpackReadRepository
{
    /** @return array{category: string, categories: list<array<string, mixed>>, data: list<array<string, mixed>>, meta: array<string, int>} */
    public function page(int $userId, BackpackCategory $category, int $page, int $perPage): array;
}
