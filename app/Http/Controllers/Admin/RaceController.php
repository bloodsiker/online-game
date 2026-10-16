<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Race\Infrastructure\Persistence\Models\RaceAvatar;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use App\Modules\User\Infrastructure\Persistence\Models\UserProfile;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RaceController extends Controller
{
    public function list(): View
    {
        $listRaces = Race::orderByDesc('id')->get();

        return view('admin.race.list', compact('listRaces'));
    }

    public function create(Request $request, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $race = new Race;
            $this->fillRace($race, $request, $translationEditor);
            $race->save();

            return redirect()->route('admin.race.info', $race->id)->with('success', 'Раса создана.');
        }

        return view('admin.race.create');
    }

    public function info(Request $request, Race $race, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillRace($race, $request, $translationEditor);
            $race->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $avatarCatalogReady = Schema::hasTable('race_avatars');
        $avatars = $avatarCatalogReady ? $race->avatars()->get() : collect();

        return view('admin.race.info', compact('race', 'avatars', 'avatarCatalogReady'));
    }

    public function uploadAvatar(Request $request, Race $race, AdminImageStorage $imageStorage): RedirectResponse
    {
        if (! Schema::hasTable('race_avatars')) {
            return redirect()->route('admin.race.info', $race)
                ->with('error', 'Для каталога аватаров ещё не применена миграция базы данных.');
        }

        $data = $request->validate([
            'sex' => ['required', 'in:male,female'],
            'name' => ['required', 'string', 'max:100'],
            'image' => ['required', 'image', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $imagePath = $imageStorage->storeOnPublicDisk($request->file('image'), 'race-avatars');

        $race->avatars()->create([
            'sex' => $data['sex'],
            'name' => $data['name'],
            'image_path' => $imagePath,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.race.info', $race)->with('success', 'Аватар добавлен.');
    }

    public function updateAvatar(Request $request, Race $race, RaceAvatar $avatar): RedirectResponse
    {
        abort_unless(Schema::hasTable('race_avatars'), 404);
        abort_unless((int) $avatar->race_id === (int) $race->id, 404);

        $data = $request->validate([
            'sex' => ['required', 'in:male,female'],
        ]);

        $avatar->update(['sex' => $data['sex']]);

        return redirect()->route('admin.race.info', $race)->with('success', 'Пол аватара обновлён.');
    }

    public function deleteAvatar(Race $race, RaceAvatar $avatar): RedirectResponse
    {
        abort_unless(Schema::hasTable('race_avatars'), 404);
        abort_unless((int) $avatar->race_id === (int) $race->id, 404);

        $publicUrl = $avatar->image_url;
        $avatar->delete();

        if (! UserProfile::query()->where('avatar', $publicUrl)->exists()) {
            Storage::disk('public')->delete($avatar->image_path);
        }

        return redirect()->route('admin.race.info', $race)->with('success', 'Аватар удалён.');
    }

    private function fillRace(Race $race, Request $request, ContentTranslationEditor $translationEditor): void
    {
        $translationEditor->fill($race, $request, ['name']);
        $race->strength = (float) $request->input('strength', 0);
        $race->agility = (float) $request->input('agility', 0);
        $race->intuition = (float) $request->input('intuition', 0);
        $race->wisdom = (float) $request->input('wisdom', 0);
        $race->intelligence = (float) $request->input('intelligence', 0);
        $race->endurance = (float) $request->input('endurance', 0);
        $race->free_stats = (int) $request->input('free_stats', 0);
    }
}
