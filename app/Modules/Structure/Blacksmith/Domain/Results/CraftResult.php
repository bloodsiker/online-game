<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Results;

final readonly class CraftResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public bool $resourcesConsumed = true,
        public ?int $craftedShareItemId = null,
    ) {}

    public static function notEnoughResources(): self
    {
        return new self(
            success: false,
            message: 'Не достаточно ресурсов для крафта',
            resourcesConsumed: false,
        );
    }

    public static function success(int $craftedShareItemId, string $craftedItemName): self
    {
        return new self(
            success: true,
            message: sprintf('Успешний крафт. Получено %s', $craftedItemName),
            craftedShareItemId: $craftedShareItemId,
        );
    }

    public static function failure(): self
    {
        return new self(
            success: false,
            message: 'Не удачный крафт',
        );
    }
}
