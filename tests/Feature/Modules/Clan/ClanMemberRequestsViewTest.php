<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus;
use App\Modules\Clan\Domain\Models\ClanRole;
use Tests\TestCase;

final class ClanMemberRequestsViewTest extends TestCase
{
    public function test_requests_have_a_separate_frame_and_content_sized_actions(): void
    {
        $html = view('clan::member', [
            'membership' => (object) ['role' => (new ClanRole)->forceFill(['permissions' => 0, 'is_leader' => false])],
            'clan' => (object) ['experience' => 100],
            'onlineCount' => 0,
            'canInvite' => true,
            'rows' => collect([[
                'type' => 'request', 'id' => 7, 'status' => ClanJoinRequestStatus::REQUEST,
                'user' => (object) [
                    'id' => 22, 'name' => 'Кандидат', 'player' => (object) ['lvl' => 6], 'currentLocation' => null,
                    'clanMembership' => (object) ['clan' => (object) ['id' => 9, 'name' => 'Другой клан', 'icon' => 'clan_icons/other.gif']],
                ],
            ]]),
        ])->render();

        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//table[@id="clan-member-requests"]/preceding-sibling::table[1][@id="clan-member-roster"]')->length);
        $this->assertSame(1, $xpath->query('//table[@id="clan-member-requests"]//td[@class="tbl-usi_label-center"]')->length);
        $this->assertSame(2, $xpath->query('//table[@id="clan-member-requests"]//td[contains(@class, "clan-requests-actions")]')->length);
        $this->assertStringContainsString(route('clan.application.accept', ['application' => 7]), $html);
        $this->assertStringContainsString(route('clan.application.decline', ['application' => 7]), $html);
        $this->assertStringContainsString(route('clan.public', ['clan' => 9]), $html);
        $this->assertStringContainsString('clan_icons/other.gif', $html);
        $this->assertStringContainsString(route('info.user', ['id' => 22]), $html);
        $this->assertStringContainsString('users-arrow.gif', $html);
    }

    public function test_requests_frame_is_hidden_when_there_are_no_requests(): void
    {
        $html = view('clan::partials.member-requests', ['clanRequests' => collect(), 'canInvite' => false])->render();
        $this->assertStringNotContainsString('clan-member-requests', $html);
    }
}
