<?php

declare(strict_types=1);

namespace App\Modules\Location\Domain\Traps;

final readonly class TrapOutcome
{
    /** @param list<string> $messages */
    public function __construct(
        public array $messages = [],
        public bool $died = false,
    ) {}

    public static function nothing(): self
    {
        return new self;
    }

    public function message(): ?string
    {
        return $this->messages === [] ? null : implode(' ', $this->messages);
    }
}
