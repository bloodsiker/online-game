<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use Tests\TestCase;

final class ClanSkillsViewTest extends TestCase
{
    public function test_skill_cards_use_inset_frames_and_keep_skill_information(): void
    {
        $html = view('clan::skills', [
            'clan' => (object) ['name' => 'Клан', 'lvl' => 2, 'points' => 100],
            'learnedMap' => [1 => 2],
            'definitions' => collect([
                (object) ['id' => 1, 'name' => 'Защита клана', 'description' => 'Описание навыка', 'icon' => null, 'max_level' => 3, 'levels' => collect()],
                (object) ['id' => 2, 'name' => 'Сила клана', 'description' => 'Второй навык', 'icon' => null, 'max_level' => 3, 'levels' => collect()],
            ]),
        ])->render();

        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(2, $xpath->query('//table[contains(@class,"skill-card")]')->length);
        $this->assertSame(2, $xpath->query('//table[contains(@class,"skill-card")]//td[@class="common-inset-bg"]')->length);
        $this->assertStringContainsString('common-inset-tl.png', $html);
        $this->assertStringContainsString('Защита клана', $html);
        $this->assertStringContainsString('Описание навыка', $html);
        $this->assertStringContainsString(__('Не изучен'), $html);
        $this->assertStringNotContainsString('<div class="skill-card">', $html);
    }
}
