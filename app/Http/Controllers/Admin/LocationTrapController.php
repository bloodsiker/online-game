<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Location\Application\Services\TrapService;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationTrap;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationTrapController extends Controller
{
    private const DIRECTIONS = ['north', 'south', 'east', 'west'];

    public function __construct(private readonly TrapService $traps) {}

    public function list(): View
    {
        $traps = LocationTrap::with('location')->orderByDesc('id')->get();

        return view('admin.location_trap.list', ['traps' => $traps, 'types' => $this->traps->types()]);
    }

    public function create(Request $request, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $trap = new LocationTrap;
            $this->fillTrap($trap, $request, $translationEditor);
            $trap->save();

            return redirect()->route('admin.location-trap.info', $trap->id)->with('success', 'Ловушка создана.');
        }

        return view('admin.location_trap.create', $this->formData(null));
    }

    public function info(Request $request, LocationTrap $locationTrap, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillTrap($locationTrap, $request, $translationEditor);
            $locationTrap->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        return view('admin.location_trap.info', $this->formData($locationTrap));
    }

    public function delete(LocationTrap $locationTrap): RedirectResponse
    {
        $locationTrap->delete();

        return redirect()->route('admin.location-traps')->with('success', 'Ловушка удалена.');
    }

    /** @return array<string, mixed> */
    private function formData(?LocationTrap $trap): array
    {
        return [
            'trap' => $trap,
            'types' => $this->traps->types(),
            'directions' => self::DIRECTIONS,
            'effects' => Effect::query()->where('type', 'debuff')->orderBy('slug')->get(),
        ];
    }

    private function fillTrap(LocationTrap $trap, Request $request, ContentTranslationEditor $translationEditor): void
    {
        $types = $this->traps->types();
        $data = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'direction' => ['nullable', 'in:'.implode(',', self::DIRECTIONS)],
            'type' => ['required', 'in:'.implode(',', array_keys($types))],
            'config' => ['nullable', 'array'],
        ]);

        $config = [];
        foreach ($types[$data['type']]->configFields() as $field) {
            $value = $request->input('config.'.$field['key']);
            if ($value === null || $value === '') {
                continue;
            }
            $config[$field['key']] = match ($field['kind']) {
                'number' => is_numeric($value) ? $value + 0 : $field['default'],
                default => (string) $value,
            };
        }

        $translationEditor->fill($trap, $request, ['name']);
        $trap->fill([
            'location_id' => (int) $data['location_id'],
            'direction' => $data['direction'] ?: null,
            'type' => $data['type'],
            'config' => $config,
            'is_active' => $request->boolean('is_active'),
        ]);
    }
}
