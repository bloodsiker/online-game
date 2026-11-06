<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\DTOs;

final readonly class LibrarySeoData
{
    /** @param array<string, string> $alternates */
    public function __construct(
        public string $heading,
        public string $title,
        public string $description,
        public string $canonical,
        public array $alternates,
        public string $image,
        public string $type = 'website',
        public string $robots = 'index, follow',
        public ?string $publishedAt = null,
        public ?string $modifiedAt = null,
    ) {}
}
