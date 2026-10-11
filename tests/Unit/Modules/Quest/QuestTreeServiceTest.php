<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Quest;

use App\Modules\Quest\Application\Services\QuestTreeService;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use Tests\TestCase;

class QuestTreeServiceTest extends TestCase
{
    public function test_previous_quest_builds_ordered_chains_and_branches(): void
    {
        $result = (new QuestTreeService)->build(collect([
            $this->quest(id: 3, previous: 1),
            $this->quest(id: 2, previous: 1),
            $this->quest(id: 1),
            $this->quest(id: 4),
        ]));

        $this->assertSame(4, $result['questCount']);
        $this->assertSame(2, $result['linkCount']);
        $this->assertCount(2, $result['trees']);
        $this->assertSame(1, $result['trees'][0]['quest']->id);
        $this->assertSame([2, 3], array_map(fn ($node) => $node['quest']->id, $result['trees'][0]['children']));
    }

    public function test_grouping_is_separate_from_required_previous_quest(): void
    {
        $quests = collect([$this->quest(id: 1), $this->quest(id: 2), $this->quest(id: 3, previous: 1, parent: 2)]);
        $result = (new QuestTreeService)->build(quests: $quests, relation: 'parent_quest_id');

        $this->assertEmpty($result['trees'][0]['children']);
        $this->assertSame(3, $result['trees'][1]['children'][0]['quest']->id);
    }

    public function test_search_preserves_the_whole_matching_chain(): void
    {
        $quests = collect([$this->quest(id: 1), $this->quest(id: 2, previous: 1, title: 'Поиск трав'), $this->quest(id: 3)]);
        foreach (['2', 'ТРАВ'] as $search) {
            $result = (new QuestTreeService)->build(quests: $quests, search: $search);
            $this->assertCount(1, $result['trees']);
            $this->assertSame(1, $result['trees'][0]['quest']->id);
            $this->assertSame(2, $result['trees'][0]['children'][0]['quest']->id);
        }
        $this->assertEmpty((new QuestTreeService)->build(quests: $quests, search: 'нет такого')['trees']);
    }

    public function test_cycles_are_visible_and_stop_recursion_even_with_a_lower_id_descendant(): void
    {
        $result = (new QuestTreeService)->build(collect([
            $this->quest(id: 1, previous: 2),
            $this->quest(id: 2, previous: 3),
            $this->quest(id: 3, previous: 2),
        ]));

        $this->assertCount(1, $result['trees']);
        $root = $result['trees'][0];
        $this->assertSame(2, $root['quest']->id);
        $this->assertSame(1, $root['children'][0]['quest']->id);
        $this->assertTrue($root['children'][1]['children'][0]['cycle']);
        $this->assertEmpty($root['children'][1]['children'][0]['children']);

        $self = (new QuestTreeService)->build(collect([$this->quest(id: 5, previous: 5)]));
        $this->assertTrue($self['trees'][0]['children'][0]['cycle']);
    }

    public function test_missing_parent_is_visible_and_empty_catalog_is_supported(): void
    {
        $result = (new QuestTreeService)->build(collect([$this->quest(id: 1, previous: 99)]));
        $this->assertSame(99, $result['trees'][0]['missing_parent_id']);
        $this->assertSame(0, $result['linkCount']);
        $this->assertSame(['trees' => [], 'questCount' => 0, 'linkCount' => 0], (new QuestTreeService)->build(collect()));
    }

    public function test_tree_nodes_render_edit_links_escaped_titles_and_warnings(): void
    {
        $result = (new QuestTreeService)->build(collect([
            $this->quest(id: 1, previous: 99, title: '<script>test</script>'),
            $this->quest(id: 2, previous: 1),
        ]));
        $html = view('admin.quest._tree_node', ['node' => $result['trees'][0], 'search' => '2'])->render();

        $this->assertStringContainsString(route('admin.quest.info', 2), $html);
        $this->assertStringContainsString('&lt;script&gt;test&lt;/script&gt;', $html);
        $this->assertStringContainsString('Связанный квест #99 не найден.', $html);
        $this->assertStringContainsString('quest-tree-card--match', $html);
        $this->assertStringContainsString('quest-tree-children', $html);
    }

    private function quest(int $id, ?int $previous = null, ?int $parent = null, string $title = 'Квест'): Quest
    {
        $quest = (new Quest)->forceFill([
            'id' => $id,
            'title' => $title,
            'type' => 'one_time',
            'after_quest_id' => $previous,
            'parent_quest_id' => $parent,
        ]);
        $quest->setRelation('startNpc', null);

        return $quest;
    }
}
