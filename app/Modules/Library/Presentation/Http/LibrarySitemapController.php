<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Http;

use App\Modules\Library\Presentation\Services\LibrarySitemap;
use Illuminate\Http\Response;

final class LibrarySitemapController
{
    public function index(LibrarySitemap $sitemap): Response
    {
        return response()->view('library::sitemap', ['entries' => $sitemap->entries()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
