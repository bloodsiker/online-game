<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\Structure\Blacksmith\Domain\Results\CraftResult;
use App\Modules\Structure\Blacksmith\Domain\Results\SalvageResult;
use App\Modules\Structure\Blacksmith\Domain\Results\UpgradeResult;

final readonly class BlacksmithActionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
        public bool $success = false,
        public bool $destroyed = false,
    ) {}

    public static function fromCraftResult(CraftResult $result): self
    {
        return new self(
            ok: $result->resourcesConsumed,
            message: $result->message,
            success: $result->success,
        );
    }

    public static function fromSalvageResult(SalvageResult $result): self
    {
        return new self(
            ok: $result->success,
            message: $result->message,
            success: $result->success,
        );
    }

    public static function fromUpgradeResult(UpgradeResult $result): self
    {
        return new self(
            ok: $result->success,
            message: $result->message,
            success: $result->success,
            destroyed: $result->destroyed,
        );
    }
}
