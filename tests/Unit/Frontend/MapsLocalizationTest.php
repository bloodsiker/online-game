<?php

declare(strict_types=1);

namespace Tests\Unit\Frontend;

use DOMDocument;
use DOMXPath;
use Illuminate\Routing\Route;
use Tests\TestCase;

class MapsLocalizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $route = (new Route('GET', 'map/{slug}', fn () => null))->name('map.public');
        request()->setRouteResolver(fn () => $route);
    }

    public function test_city_cell_names_and_legend_follow_selected_language(): void
    {
        app()->setLocale('uk');
        $html = view('maps.city.main.map')->render();
        foreach (['Місто Нейрин', 'Міська брама', 'Вежа магів', 'Торгова площа', 'Джерело життя', 'Темниця', 'Умовні позначення', 'Проходи до інших областей', 'Шепітний Ліс'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        foreach (['<cite>Городские ворота</cite>', '<cite>Башня магов</cite>', '<cite>Темница</cite>'] as $text) {
            $this->assertStringNotContainsString($text, $html);
        }
        $this->assertStringContainsString('<html lang="uk">', $html);
        app()->setLocale('ru');
        $this->assertStringContainsString('<cite>Городские ворота</cite>', view('maps.city.main.frame')->render());
    }

    public function test_shared_map_translates_configuration_titles_and_legend_labels(): void
    {
        app()->setLocale('uk');
        $html = view('maps.subcity.ash_foothills.map')->render();
        $this->assertStringContainsString('Попелясті Передгір&#039;я', $html);
        $this->assertStringContainsString('Ожила околиця', $html);
        $this->assertStringNotContainsString('>Пепельные Предгорья<', $html);
        $this->assertStringContainsString('Підскельна дорога', view('maps.carrigmore.podskalnaya_doroga.map')->render());
        $this->assertStringContainsString('Завалена Штольня', view('maps.carrigmore.zavalennaya_stolnya.map')->render());
    }

    public function test_localization_preserves_all_cell_ids_borders_and_transition_links(): void
    {
        foreach (['city.main', 'city.sewers', 'subcity.main', 'subcity.watch_hills', 'subcity.overgrown_road', 'subcity.granite_pass', 'subcity.ash_foothills', 'subcity.zabytiy_kurgan', 'carrigmore.main', 'carrigmore.podskalnaya_doroga', 'carrigmore.zavalennaya_stolnya', 'catacomb_sacrifice'] as $map) {
            $snapshots = [];
            foreach (['ru', 'uk'] as $locale) {
                app()->setLocale($locale);
                $html = view('maps.'.$map.'.map')->render();
                $dom = new DOMDocument;
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
                $xpath = new DOMXPath($dom);
                $snapshot = [];
                foreach ($xpath->query('//*[@id] | //a[@href]') as $node) {
                    $snapshot[] = [$node->nodeName, $node->getAttribute('id'), $node->getAttribute('class'), $node->getAttribute('href')];
                }
                $snapshots[$locale] = $snapshot;
            }
            $this->assertSame($snapshots['ru'], $snapshots['uk'], $map);
        }
    }
}
