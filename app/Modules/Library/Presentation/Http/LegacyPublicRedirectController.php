<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Library\Presentation\Services\LibraryUrls;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Http\RedirectResponse;

/**
 * Постоянные (301) редиректы со старых публичных адресов, которые ещё помнят поисковики,
 * на их точные замены. Адреса без точной замены остаются 404.
 */
final class LegacyPublicRedirectController extends Controller
{
    /** Старый адрес раздела → slug категории библиотеки. */
    private const CATEGORIES = [
        'artifacts' => 'artefakty',
        'items' => 'snariazhenie',
    ];

    public function __construct(private readonly LibraryUrls $urls) {}

    public function category(string $section): RedirectResponse
    {
        $category = LibraryCategory::query()
            ->where('is_active', true)
            ->where('slug', self::CATEGORIES[$section] ?? '')
            ->firstOrFail();

        return redirect()->to($this->urls->category($category), 301);
    }

    public function location(int $id): RedirectResponse
    {
        $slug = Location::query()->with('map:id,slug')->findOrFail($id)->map?->slug;
        abort_if($slug === null || $slug === '', 404);

        return redirect()->route('map.public', ['slug' => $slug, 'highlight_location' => $id], 301);
    }
}
