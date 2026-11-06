<?php

declare(strict_types=1);

namespace App\Modules\Interface\Presentation\Services;

use App\Modules\Forum\Presentation\Services\ForumSitemap;
use App\Modules\Library\Presentation\Services\LibrarySitemap;

final readonly class SiteSitemap
{
    public function __construct(private LibrarySitemap $library, private ForumSitemap $forum) {}

    /** @return list<array{url: string, lastmod: ?string}> */
    public function entries(): array
    {
        return [...$this->library->entries(), ...$this->forum->entries()];
    }
}
