<?php

declare(strict_types=1);

namespace App\Modules\Weather\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Weather\Application\Services\WeatherService;
use App\Modules\Weather\Domain\Enums\WeatherType;
use App\Modules\Weather\Infrastructure\Persistence\Models\MapWeather;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class WeatherAdminController extends Controller
{
    public function index(): View
    {
        $maps = DB::table('maps')->orderBy('id')->get(['id', 'name'])
            ->map(static function (object $map): object {
                $names = json_decode((string) $map->name, true);
                $map->title = is_array($names) ? (string) ($names['ru'] ?? reset($names)) : (string) $map->name;

                return $map;
            });
        $current = MapWeather::query()->get()->keyBy('map_id');
        $weights = DB::table('map_weather_weights')->get()
            ->groupBy('map_id')
            ->map(static fn ($rows) => $rows->pluck('weight', 'weather'));

        return view('weather::admin', [
            'maps' => $maps,
            'current' => $current,
            'weights' => $weights,
            'types' => WeatherType::cases(),
        ]);
    }

    public function force(Request $request, int $map, WeatherService $weather): RedirectResponse
    {
        $data = $request->validate([
            'weather' => ['nullable', Rule::enum(WeatherType::class)],
            'minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);
        $type = ($data['weather'] ?? null) !== null ? WeatherType::from((string) $data['weather']) : null;
        $weather->force($map, $type, (int) $data['minutes']);

        return back()->with('success', $type === null
            ? 'На карте #'.$map.' ясно ('.$data['minutes'].' мин).'
            : 'На карте #'.$map.' включено «'.$type->label().'» на '.$data['minutes'].' мин.');
    }

    public function saveWeights(Request $request, int $map): RedirectResponse
    {
        $data = $request->validate([
            'weights' => ['array'],
            'weights.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
        $now = now();
        foreach (WeatherType::cases() as $type) {
            if ($type === WeatherType::RAINBOW) {
                continue; // радуга приходит только после грозы
            }
            $weight = (int) ($data['weights'][$type->value] ?? 0);
            if ($weight > 0) {
                DB::table('map_weather_weights')->updateOrInsert(
                    ['map_id' => $map, 'weather' => $type->value],
                    ['weight' => $weight, 'updated_at' => $now, 'created_at' => $now],
                );
            } else {
                DB::table('map_weather_weights')->where('map_id', $map)->where('weather', $type->value)->delete();
            }
        }

        return back()->with('success', 'Веса погоды для карты #'.$map.' сохранены.');
    }
}
