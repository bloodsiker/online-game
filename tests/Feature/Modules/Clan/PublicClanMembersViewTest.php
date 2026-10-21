<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use Tests\TestCase;

final class PublicClanMembersViewTest extends TestCase
{
    public function test_members_have_info_icons_and_levels_beside_names_without_a_history_tab(): void
    {
        $html = view('clan::public', [
            'clan' => (object) ['id' => 1, 'name' => 'Клан', 'icon' => null],
            'mode' => 'members',
            'members' => collect([[
                'user' => (object) ['id' => 12, 'name' => 'Игрок', 'player' => (object) ['lvl' => 35]],
                'role' => (object) ['name' => 'Участник'], 'is_online' => true,
            ]]),
        ])->render();

        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(3, $xpath->query('//table[@class="clan-members"]/thead/tr/th')->length);
        $this->assertSame(1, $xpath->query('//table[@class="clan-members"]//img[contains(@src,"player_info.gif")]')->length);
        $this->assertStringContainsString('Игрок&nbsp;[35]</a>', $html);
        $this->assertStringContainsString('height=800', $html);
        $this->assertStringNotContainsString('mode=history', $html);
    }
}
