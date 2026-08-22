<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class AdminBreadcrumbTest extends TestCase
{
    public function test_nested_admin_page_links_back_to_its_section(): void
    {
        $html = $this->renderBreadcrumb('admin.clan.info', 'Клан: Elders');

        $this->assertStringContainsString('href="'.route('admin.clans').'"', $html);
        $this->assertStringContainsString('Вернуться в раздел «Кланы»', $html);
        $this->assertStringContainsString('Клан: Elders', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    public function test_section_page_does_not_render_redundant_back_link(): void
    {
        $html = $this->renderBreadcrumb('admin.clans', 'Кланы');

        $this->assertStringNotContainsString('bx-arrow-back', $html);
        $this->assertStringContainsString('Кланы', $html);
    }

    private function renderBreadcrumb(string $routeName, string $pageTitle): string
    {
        $request = Request::create('/admin/test', 'GET');
        $route = (new Route(['GET'], '/admin/test', static fn (): null => null))->name($routeName);
        $request->setRouteResolver(static fn (): Route => $route);
        $this->app->instance('request', $request);

        return view('admin.layout.breadcrumbs', compact('pageTitle'))->render();
    }
}
