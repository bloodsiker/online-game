<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Http;

use App\Modules\Library\Presentation\Services\LibraryUrls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LegacyLibraryController
{
    public function __invoke(Request $request, LibraryUrls $urls, ?string $slug = null): RedirectResponse
    {
        // The destination depends on the viewer's language, so this redirect is not permanent.
        $url = $slug === null
            ? $urls->index(parameters: $request->query())
            : $urls->article(slug: $slug, parameters: $request->query());

        return redirect()->to($url)->withHeaders(['Cache-Control' => 'private, no-store']);
    }
}
