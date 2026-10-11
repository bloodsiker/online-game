<?php

declare(strict_types=1);

namespace App\Modules\Quest\Application\Services;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use Illuminate\Support\Collection;

class QuestTreeService
{
    public function getTree(string $relation = 'after_quest_id', string $search = ''): array
    {
        $quests = Quest::query()
            ->without('objectives')
            ->with('startNpc:id,name')
            ->get(['id', 'title', 'type', 'is_active', 'is_finish', 'start_npc_id', 'parent_quest_id', 'after_quest_id']);

        return $this->build(quests: $quests, relation: $relation, search: $search);
    }

    public function build(Collection $quests, string $relation = 'after_quest_id', string $search = ''): array
    {
        $relation = $relation === 'parent_quest_id' ? $relation : 'after_quest_id';
        $quests = $quests->sortBy('id')->keyBy('id');
        $children = [];
        $roots = [];
        $linkCount = 0;

        foreach ($quests as $id => $quest) {
            $parentId = (int) $quest->{$relation};
            if ($parentId > 0 && $quests->has($parentId)) {
                $children[$parentId][] = $id;
                $linkCount++;
            } else {
                $roots[] = $id;
            }
        }

        $visited = [];
        $makeNode = function (int $id, array $path = []) use (&$makeNode, &$visited, $quests, $children, $relation): array {
            $cycle = isset($path[$id]);
            $quest = $quests->get($id);
            $parentId = (int) $quest->{$relation};
            $node = [
                'quest' => $quest,
                'cycle' => $cycle,
                'missing_parent_id' => $parentId > 0 && ! $quests->has($parentId) ? $parentId : null,
                'children' => [],
            ];
            if ($cycle) {
                return $node;
            }

            $visited[$id] = true;
            $path[$id] = true;
            foreach ($children[$id] ?? [] as $childId) {
                $node['children'][] = $makeNode($childId, $path);
            }

            return $node;
        };

        $trees = [];
        foreach ($roots as $id) {
            $trees[] = $makeNode($id);
        }
        // A cycle has no root. Include its component instead of silently hiding it.
        foreach ($quests->keys() as $id) {
            if (! isset($visited[$id])) {
                $path = [];
                $cycleRoot = $id;
                while (! isset($path[$cycleRoot])) {
                    $path[$cycleRoot] = true;
                    $cycleRoot = (int) $quests->get($cycleRoot)->{$relation};
                }
                $trees[] = $makeNode($cycleRoot);
            }
        }

        $search = mb_strtolower(trim($search));
        if ($search !== '') {
            $matches = function (array $node) use (&$matches, $search): bool {
                if (str_contains(mb_strtolower($node['quest']->title), $search)
                    || (string) $node['quest']->id === $search) {
                    return true;
                }
                foreach ($node['children'] as $child) {
                    if ($matches($child)) {
                        return true;
                    }
                }

                return false;
            };
            $trees = array_values(array_filter($trees, $matches));
        }

        return ['trees' => $trees, 'questCount' => $quests->count(), 'linkCount' => $linkCount];
    }
}
