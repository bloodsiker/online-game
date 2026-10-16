<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use Tests\TestCase;

class ClanBossBattleRoutesTest extends TestCase
{
    public function test_clan_boss_battle_has_dedicated_page_and_attack_endpoints(): void
    {
        $pageRoute = app('router')->getRoutes()->getByName('fight.clan-boss');
        $attackRoute = app('router')->getRoutes()->getByName('fight.clan-boss.attack');

        $this->assertNotNull($pageRoute);
        $this->assertSame('clan-boss/battle/{id}', $pageRoute->uri());
        $this->assertContains('auth', $pageRoute->gatherMiddleware());

        $this->assertNotNull($attackRoute);
        $this->assertSame('clan-boss/battle/{id}/attack/{monsterId}/{action}', $attackRoute->uri());
        $this->assertContains('auth', $attackRoute->gatherMiddleware());
        $this->assertContains('throttle:120,1', $attackRoute->gatherMiddleware());
        $this->assertTrue(view()->exists('battle::clan_boss'));
    }
}
