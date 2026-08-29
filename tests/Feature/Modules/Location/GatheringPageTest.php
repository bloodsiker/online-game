<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Location;

use App\Modules\Location\Application\UseCases\GetGatheringPage;
use App\Modules\Location\Domain\Services\GatheringService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Mockery;
use Tests\TestCase;

class GatheringPageTest extends TestCase
{
    public function test_it_builds_shared_map_gathering_page(): void
    {
        $page = $this->makeUseCase()->execute($this->makeUser());

        $this->assertSame(118, $page->locationId);
        $this->assertSame(7, $page->mapId);
        $this->assertSame('Заросшая дорога', $page->mapName);
        $this->assertSame('Травник', $page->professions[0]['name']);
        $this->assertSame('Лечебная трава', $page->nodes[0]['name']);
        $this->assertSame('/location', parse_url($page->backUrl, PHP_URL_PATH));
    }

    public function test_gathering_view_uses_server_endpoints_and_double_click_nodes(): void
    {
        $this->withoutVite();
        $page = $this->makeUseCase()->execute($this->makeUser());
        $html = view('location::gathering', ['page' => $page])->render();

        $this->assertStringContainsString('id="gathering-field"', $html);
        $this->assertStringContainsString('id="gathering-canvas"', $html);
        $this->assertStringContainsString('overflow: scroll', $html);
        $this->assertStringContainsString('const urls =', $html);
        $this->assertStringContainsString('__NODE__', $html);
        $this->assertStringContainsString("addEventListener('dblclick'", $html);
        $this->assertStringContainsString('id="gathering-selection"', $html);
        $this->assertStringContainsString("addEventListener('click'", $html);
        $this->assertStringContainsString('function renderSelection()', $html);
        $this->assertStringContainsString('id="gathering-work"', $html);
        $this->assertStringContainsString('id="gathering-cancel"', $html);
        $this->assertStringContainsString('function cancelGathering()', $html);
        $this->assertStringContainsString('setMapHiddenForGathering(true)', $html);
        $this->assertStringContainsString('setMapHiddenForGathering(false)', $html);
        $this->assertStringContainsString('gathering-tooltip-inner', $html);
        $this->assertStringContainsString('gathering-tooltip-head', $html);
        $this->assertStringContainsString('Требуется умение', $html);
        $this->assertStringContainsString('Math.round(progress)', $html);
        $this->assertStringContainsString('gathering-busy-pulse', $html);
        $this->assertStringContainsString('gathering-node-gatherers', $html);
        $this->assertStringContainsString('id="gathering-notice"', $html);
        $this->assertStringNotContainsString('gathering-node-badge', $html);
        $this->assertStringNotContainsString('is-locked', $html);
        $this->assertStringNotContainsString('Угодье карты · добыча ресурсов', $html);
        $this->assertStringNotContainsString('gathering-head-meta', $html);
        $this->assertStringContainsString('/prototypes/map.png', $html);
        $this->assertStringContainsString('Тяните карту или используйте прокрутку', $html);
        $this->assertStringContainsString("addEventListener('pointerdown'", $html);
        $this->assertStringContainsString('scrollLeft', $html);
        $this->assertStringContainsString("window.Echo.channel('gathering.map.' + mapId)", $html);
        $this->assertStringContainsString("listen('.gathering.map.updated'", $html);
        $this->assertStringNotContainsString('window.setInterval(refreshState, 2000)', $html);
        $this->assertStringNotContainsString('← Вернуться на локацию', $html);
        $this->assertStringNotContainsString('Тихая поляна', $html);
        $this->assertStringNotContainsString('Здесь растут лечебные травы.', $html);
    }

    public function test_gathering_routes_are_registered(): void
    {
        $this->assertSame('/gathering', parse_url(route('gathering'), PHP_URL_PATH));
        $this->assertSame('/gathering/state', parse_url(route('gathering.state'), PHP_URL_PATH));
        $this->assertSame('/gathering/complete', parse_url(route('gathering.complete'), PHP_URL_PATH));
    }

    private function makeUseCase(): GetGatheringPage
    {
        $service = Mockery::mock(GatheringService::class);
        $service->shouldReceive('state')->once()->andReturn([
            'enabled' => true,
            'message' => null,
            'professions' => [[
                'id' => 10,
                'name' => 'Травник',
                'level' => 1,
                'experience' => 0,
                'levelExperience' => 0,
                'levelExperienceRequired' => 100,
            ]],
            'nodes' => [[
                'id' => 50,
                'name' => 'Лечебная трава',
                'image' => '/img/herb.png',
                'x' => 25,
                'y' => 40,
                'gatherTime' => 5,
            ]],
            'activeAttempt' => null,
        ]);

        return new GetGatheringPage($service);
    }

    private function makeUser(): User
    {
        $map = (new Map)->forceFill(['id' => 7, 'name' => 'Заросшая дорога']);
        $map->exists = true;

        $location = (new Location)->forceFill([
            'id' => 118,
            'map_id' => 7,
            'name' => 'Тихая поляна',
            'description' => 'Здесь растут лечебные травы.',
            'image' => null,
        ]);
        $location->exists = true;
        $location->setRelation('map', $map);

        $player = (new Player)->forceFill(['id' => 1, 'hp_now' => 100]);
        $player->exists = true;

        $user = (new User)->forceFill(['id' => 1, 'player_id' => 1, 'location_id' => 118]);
        $user->exists = true;
        $user->setRelation('currentLocation', $location);
        $user->setRelation('player', $player);

        return $user;
    }
}
