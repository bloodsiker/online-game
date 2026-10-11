<?php

declare(strict_types=1);

namespace Tests\Unit\Frontend;

use DOMDocument;
use DOMXPath;
use Illuminate\Routing\Route;
use Tests\TestCase;

class AshFoothillsMapTest extends TestCase
{
    public function test_all_parent_frame_exits_use_mode_appropriate_urls(): void
    {
        foreach (['map.public', 'on_map'] as $routeName) {
            $route = (new Route('GET', 'map', fn () => null))->name($routeName);
            request()->setRouteResolver(fn () => $route);
            $html = view('maps.subcity.main.frame')->render();
            foreach ([['1p0OH76', 101], ['Kj9u18NJl3', 883], ['9uF1sO7v', 511], ['Vr8Qm2Lp5Z', 992], ['6BJcCXXPZR', 1630]] as [$slug, $id]) {
                $expected = $routeName === 'map.public'
                    ? route('map.public', ['slug' => $slug]).'#'.$id
                    : route('on_map', ['s' => $slug]).'#'.$id;
                $this->assertStringContainsString('href="'.$expected.'"', $html);
            }
            $this->assertStringNotContainsString('href="?m=', $html);
        }
    }

    public function test_parent_map_lists_the_foothills_passage_in_public_and_game_modes(): void
    {
        foreach (['map.public', 'on_map'] as $routeName) {
            $route = (new Route('GET', 'map', fn () => null))->name($routeName);
            request()->setRouteResolver(fn () => $route);
            $html = view('maps.subcity.main.map')->render();
            $expected = $routeName === 'map.public'
                ? route('map.public', ['slug' => '6BJcCXXPZR']).'#1630'
                : route('on_map', ['s' => '6BJcCXXPZR']).'#1630';

            $this->assertStringContainsString('Проходы в другие области', $html);
            $this->assertStringContainsString('href="'.$expected.'">Пепельные Предгорья</a>', $html);
            $this->assertStringContainsString('onclick="mark_l(395,2)"', $html);
        }
    }

    public function test_map_frame_renders_all_locations_walls_and_empty_padding(): void
    {
        $html = view('maps.subcity.ash_foothills.frame')->render();
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new DOMXPath($dom);

        $this->assertSame(70, $xpath->query('//div[starts-with(@id,"l") and contains(@class,"s2box")]')->length);
        foreach (range(1630, 1699) as $id) {
            $this->assertSame(1, $xpath->query('//div[@id="l'.$id.'"]')->length);
        }
        $this->assertSame(9, $xpath->query('//table[@id="m0"]/tbody/tr[td]')->length);
        $this->assertSame(28, $xpath->query('//table[@id="m0"]/tbody/tr[td][1]/td')->length);
        $this->assertStringContainsString('br', $xpath->query('//div[@id="l1694"]')->item(0)->getAttribute('class'));
        $this->assertStringContainsString('bl', $xpath->query('//div[@id="l1691"]')->item(0)->getAttribute('class'));
        $this->assertStringContainsString('Gh865Vpo', $xpath->query('//div[@id="l1630"]/a[@class="aeast"]')->item(0)->getAttribute('href'));
    }

    public function test_public_map_renders_legend_and_public_exit_link(): void
    {
        $route = (new Route('GET', 'map/{slug}', fn () => null))->name('map.public');
        request()->setRouteResolver(fn () => $route);
        $html = view('maps.subcity.ash_foothills.map')->render();

        $this->assertStringContainsString('Пепельные Предгорья', $html);
        $this->assertStringContainsString('Ожившая окраина', $html);
        $this->assertStringContainsString(route('map.public', ['slug' => 'Gh865Vpo']).'#395', $html);
        $this->assertStringNotContainsString('/on-map?s=', $html);
    }
}
