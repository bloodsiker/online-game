<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\News;

use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

final class HomepageAssetsTest extends TestCase
{
    public function test_homepage_renders_gallery_and_tooltips_without_loading_legacy_portal_scripts(): void
    {
        $this->withoutVite();
        view()->share('errors', new ViewErrorBag);
        foreach (['ru', 'uk'] as $locale) {
            app()->setLocale($locale);
            $html = view('news::index', [
                'selectedNews' => null, 'newsPosts' => collect(), 'topPlayers' => collect(),
                'topClans' => collect(), 'hotForumTopics' => collect(), 'onlineCount' => 0,
            ])->render();
            foreach (['jquery', 'vendors.min.js', 'backbone.js', 'underscore.js', 'cufon', 'Cambria_400.font.js',
                'fancybox.min.js', 'common.js', 'simple_alt.js', 'lib.min.js', 'main/js/main.js', 'counter.js'] as $legacy) {
                $this->assertStringNotContainsString($legacy, $html);
            }
            $this->assertStringContainsString('js/item_tooltip.js', $html);
            $this->assertStringNotContainsString('src="index.html"', $html);
            $this->assertStringNotContainsString('src=""', $html);
            $this->assertSame(7, substr_count($html, 'data-gallery-index='));
            $this->assertStringContainsString('id="homepage-lightbox"', $html);
            $this->assertStringContainsString(__('homepage.gallery.close'), $html);
            $this->assertStringContainsString(__('homepage.gallery.image', ['number' => 7]), $html);
        }
    }
}
