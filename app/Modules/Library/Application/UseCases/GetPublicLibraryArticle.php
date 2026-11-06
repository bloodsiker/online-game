<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;

final class GetPublicLibraryArticle
{
    public function execute(string $slug): LibraryArticle
    {
        return LibraryArticle::query()->published()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with(['category', 'links'])->where('slug', $slug)->firstOrFail();
    }
}
