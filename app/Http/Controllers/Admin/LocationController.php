<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Domain\Enums\LocationItemInteractionType;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Domain\Services\MapMonstersCache;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LocationController extends Controller
{
    private readonly AdminImageStorage $imageStorage;

    public function __construct(?AdminImageStorage $imageStorage = null)
    {
        $this->imageStorage = $imageStorage ?? new AdminImageStorage;
    }

    public function list(): View
    {
        $listLocations = Location::with('map')->orderByDesc('id')->get();

        return view('admin.location.list', compact('listLocations'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $location = new Location;
            $this->fillLocation($location, $request);
            $location->save();

            return redirect()->route('admin.location.info', $location->id)->with('success', 'Локация создана.');
        }

        return view('admin.location.create');
    }

    public function info(Request $request, Location $location): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillLocation($location, $request);
            $location->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $location->load(['map', 'northSide', 'southSide', 'eastSide', 'westSide', 'upSide', 'downSide', 'monsters']);
        $locationItems = ItemOnLocation::query()
            ->with('item.itemInfo')
            ->where('location_id', $location->id)
            ->whereNull('dungeon_session_id')
            ->orderByDesc('id')
            ->get();

        return view('admin.location.info', compact('location', 'locationItems'));
    }

    public function addMonster(Request $request, Location $location): RedirectResponse
    {
        $aggression = $request->input('aggression');

        $location->monsters()->attach($request->input('monster_id'), [
            'aggression' => $aggression !== '' && $aggression !== null ? (int) $aggression : null,
        ]);

        MapMonstersCache::flush();

        return redirect()->back()->with('success', 'Моб добавлен.');
    }

    public function updateMonsterAggression(Request $request, Location $location, Monster $monster): RedirectResponse
    {
        $aggression = $request->input('aggression');

        $location->monsters()->updateExistingPivot($monster->id, [
            'aggression' => $aggression !== '' && $aggression !== null ? (int) $aggression : null,
        ]);

        return redirect()->back()->with('success', 'Агрессия обновлена.');
    }

    public function deleteMonster(Location $location, Monster $monster): RedirectResponse
    {
        $location->monsters()->detach($monster->id);

        MapMonstersCache::flush();

        return redirect()->back()->with('success', 'Моб удалён.');
    }

    public function addItem(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'count' => ['required', 'integer', 'min:1', 'max:10000'],
            'interaction_type' => ['nullable', 'in:pickup,open_here'],
        ]);

        DB::transaction(function () use ($data, $location): void {
            $shareItem = ShareItem::query()->findOrFail($data['share_item_id']);
            $item = Item::query()->create(['share_item_id' => $shareItem->id]);

            $slot = new ItemOnLocation;
            $slot->item_id = $item->id;
            $slot->location_id = $location->id;
            $slot->dungeon_session_id = null;
            $slot->count = $data['count'];
            $slot->interaction_type = $shareItem->type === ShareItemType::CHEST
                ? LocationItemInteractionType::tryFrom($data['interaction_type'] ?? '')
                    ?? LocationItemInteractionType::OPEN_HERE
                : LocationItemInteractionType::PICKUP;
            $slot->expires_at = null;
            $slot->save();
        });

        return redirect()->back()->with('success', 'Предмет добавлен на локацию.');
    }

    public function deleteItem(Location $location, int $locationItem): RedirectResponse
    {
        $slot = ItemOnLocation::query()
            ->where('id', $locationItem)
            ->where('location_id', $location->id)
            ->firstOrFail();

        $itemId = $slot->item_id;
        $slot->delete();

        $stillUsed = Backpack::query()->where('item_id', $itemId)->exists()
            || ItemOnLocation::query()->where('item_id', $itemId)->exists();
        if (! $stillUsed) {
            Item::query()->whereKey($itemId)->delete();
        }

        return redirect()->back()->with('success', 'Предмет убран с локации.');
    }

    private function fillLocation(Location $location, Request $request): void
    {
        $location->name = $request->input('name');
        $location->description = $request->input('description');
        $location->map_id = (int) $request->input('map_id');
        $location->is_locked = (bool) $request->input('is_locked', false);
        $location->north = $request->input('north') ?: null;
        $location->south = $request->input('south') ?: null;
        $location->east = $request->input('east') ?: null;
        $location->west = $request->input('west') ?: null;
        $location->up = $request->input('up') ?: null;
        $location->down = $request->input('down') ?: null;
        $location->count_monster = (int) $request->input('count_monster', 0);
        $location->percent_respawn_monster = (int) $request->input('percent_respawn_monster', 0);
        $location->time_not_attack = (int) $request->input('time_not_attack', 0);

        if ($request->hasFile('image')) {
            $oldImage = $location->getRawOriginal('image');
            $location->image = $this->storeImage($request->file('image'));
            $this->deleteStorageImage($oldImage);
        } elseif ($request->boolean('delete_image')) {
            $this->deleteStorageImage($location->getRawOriginal('image'));
            $location->image = null;
        }

        MapMonstersCache::flush();
    }

    private function storeImage(UploadedFile $file): string
    {
        return $this->imageStorage->storeOnPublicDisk($file, 'locations');
    }
}
