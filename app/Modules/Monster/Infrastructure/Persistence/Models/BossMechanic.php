<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use App\Modules\Battle\Domain\Enums\BossMechanicType;
use App\Modules\Battle\Application\Services\Combat\Boss\BossMechanicInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BossMechanic extends Model
{
    protected $fillable = [
        'monster_id', 'mechanic_type', 'mechanic_class',
        'trigger_hp_percent', 'trigger_turn', 'config',
        'priority', 'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'mechanic_type' => BossMechanicType::class,
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    /**
     * Отримати екземпляр класу механіки через Enum
     */
    public function getInstance(): BossMechanicInterface
    {
        if (! $this->mechanic_type instanceof BossMechanicType) {
            throw new \InvalidArgumentException(
                "Invalid mechanic type: {$this->mechanic_type}"
            );
        }

        $class = $this->mechanic_type->getClass();

        if (! class_exists($class)) {
            throw new \RuntimeException(
                "Mechanic class {$class} not found for type {$this->mechanic_type->value}"
            );
        }

        // Через контейнер, а не new — щоб механіки могли отримувати додаткові
        // залежності (репозиторії тощо) поза єдиним параметром $mechanic.
        return app($class, ['mechanic' => $this]);
    }

    /**
     * Отримати назву механіки
     */
    public function getLabel(): string
    {
        return $this->mechanic_type?->getLabel() ?? 'Unknown';
    }

    /**
     * Отримати іконку механіки
     */
    public function getIcon(): string
    {
        return $this->mechanic_type?->getIcon() ?? '❓';
    }

    /**
     * Scope для фільтрації по типу
     */
    public function scopeOfType($query, BossMechanicType|string $type)
    {
        if (is_string($type)) {
            $type = BossMechanicType::tryFrom($type);
        }

        return $query->where('mechanic_type', $type);
    }
}
