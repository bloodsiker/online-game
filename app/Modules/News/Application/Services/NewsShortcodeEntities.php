<?php

declare(strict_types=1);

namespace App\Modules\News\Application\Services;

use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;

final class NewsShortcodeEntities
{
    public function search(string $type, string $term, int $page): array
    {
        $query = match ($type) {
            'item' => ShareItem::query(),
            'monster' => Monster::query(),
            'npc' => Npc::query(),
            'skill' => Skill::query(),
            'magic_skill' => MagicSkill::query(),
        };
        if ($term !== '') {
            $query->where(function ($query) use ($term): void {
                $query->searchTranslations('%'.$term.'%');
                if (ctype_digit($term)) {
                    $query->orWhere($query->getModel()->getQualifiedKeyName(), (int) $term);
                }
            });
        }
        $records = $query->orderBy('id')->offset(($page - 1) * 20)->limit(21)->get();

        return [
            'results' => $records->take(20)->map(fn ($entity): array => [
                'id' => $entity->id,
                'text' => '['.$entity->id.'] '.$entity->name,
                'image' => $entity->image ? asset($entity->image) : null,
            ])->values()->all(),
            'pagination' => ['more' => $records->count() > 20],
        ];
    }
}
