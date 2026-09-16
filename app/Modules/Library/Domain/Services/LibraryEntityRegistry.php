<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\Services;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Eloquent\Model;

final class LibraryEntityRegistry
{
    /** @var array<string, array{label: string, model: class-string<Model>, title: string}> */
    private const TYPES = [
        'monster' => ['label' => 'Монстр', 'model' => Monster::class, 'title' => 'name'],
        'reputation' => ['label' => 'Репутация', 'model' => Reputation::class, 'title' => 'name'],
        'profession' => ['label' => 'Профессия', 'model' => Skill::class, 'title' => 'name'],
        'item' => ['label' => 'Предмет', 'model' => ShareItem::class, 'title' => 'name'],
        'quest' => ['label' => 'Квест', 'model' => Quest::class, 'title' => 'title'],
        'map' => ['label' => 'Карта', 'model' => Map::class, 'title' => 'name'],
        'location' => ['label' => 'Локация', 'model' => Location::class, 'title' => 'name'],
        'npc' => ['label' => 'НПС', 'model' => Npc::class, 'title' => 'name'],
        'magic_skill' => ['label' => 'Заклинание', 'model' => MagicSkill::class, 'title' => 'name'],
        'structure' => ['label' => 'Строение', 'model' => Structure::class, 'title' => 'name'],
    ];

    /** @return array<string, string> */
    public function options(): array
    {
        return array_map(static fn (array $type): string => $type['label'], self::TYPES);
    }

    public function exists(string $type, int $id): bool
    {
        $config = self::TYPES[$type] ?? null;

        return $config !== null && $config['model']::query()->whereKey($id)->exists();
    }

    public function label(string $type, int $id): ?string
    {
        $config = self::TYPES[$type] ?? null;

        if ($config === null) {
            return null;
        }

        $value = $config['model']::query()->whereKey($id)->value($config['title']);

        return $value === null ? null : (string) $value;
    }

    public function typeLabel(string $type): string
    {
        return self::TYPES[$type]['label'] ?? $type;
    }
}
