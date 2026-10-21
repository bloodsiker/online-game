<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ClanQuestsViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('quest_clan_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('clan_id');
            $table->string('status');
        });
    }

    public function test_quest_cards_keep_objective_progress_rewards_and_cancel_action(): void
    {
        $quest = (object) [
            'id' => 4, 'title' => 'Помощь городу', 'description' => '<p>Описание задания</p>', 'reset_period' => 86400,
            'rewards' => collect(), 'objectives' => collect([(object) ['description' => 'Победить монстров']]),
        ];
        $active = (new QuestClanProgress)->forceFill(['id' => 8, 'quest_id' => 4, 'created_at' => now()]);
        $active->setRelation('quest', $quest)->setRelation('user', (object) ['name' => 'Игрок'])->setRelation('objectives', collect([
            (object) ['amount' => 3, 'questObjective' => (object) ['type' => 'kill', 'required_amount' => 10, 'description' => 'Победить монстров']],
        ]));
        $html = view('clan::quests', [
            'clan' => (object) ['id' => 1, 'name' => 'Клан'], 'isLeader' => true,
            'activeQuests' => collect([$active]), 'availableQuests' => collect([$quest]), 'history' => collect(),
        ])->render();

        $this->assertStringContainsString('common-inset-tl.png', $html);
        $this->assertStringContainsString('class="rep-progress-bar__fill" style="width: 30%;"', $html);
        $this->assertStringContainsString('3 / 10 (30%)', $html);
        $this->assertStringContainsString(route('quest.clan.cancel', 8), $html);
        $this->assertStringContainsString('<span class="butt1 pointer">', $html);
        $this->assertStringContainsString('Описание задания', $html);
        $this->assertStringContainsString('Помощь городу', $html);
    }

    public function test_empty_sections_are_displayed_inside_inset_frames(): void
    {
        $html = view('clan::quests', [
            'clan' => (object) ['id' => 1, 'name' => 'Клан'], 'isLeader' => false,
            'activeQuests' => collect(), 'availableQuests' => collect(), 'history' => collect(),
        ])->render();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(3, $xpath->query('//table[contains(@class,"quest-frame")]')->length);
        $this->assertStringContainsString(__('Нет доступных клановых квестов.'), $html);
        $this->assertStringContainsString(__('История пуста.'), $html);
    }
}
