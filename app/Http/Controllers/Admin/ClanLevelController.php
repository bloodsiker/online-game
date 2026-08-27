<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Domain\Models\ClanLevel;
use App\Modules\Clan\Domain\Services\ClanLevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClanLevelController extends Controller
{
    public function index(): View
    {
        $levels = ClanLevel::query()->orderBy('level')->get();

        return view('admin.clan_level.index', compact('levels'));
    }

    public function store(Request $request, ClanLevelService $clanLevelService): RedirectResponse
    {
        ClanLevel::create($this->validatedData($request));
        $this->refreshClanLevels($clanLevelService);

        return back()->with('success', 'Уровень клана добавлен.');
    }

    public function update(Request $request, ClanLevel $clanLevel, ClanLevelService $clanLevelService): RedirectResponse
    {
        $clanLevel->update($this->validatedData($request, $clanLevel));
        $this->refreshClanLevels($clanLevelService);

        return back()->with('success', 'Порог уровня клана сохранён.');
    }

    public function delete(ClanLevel $clanLevel, ClanLevelService $clanLevelService): RedirectResponse
    {
        if ($clanLevel->level === 1) {
            return back()->with('error', 'Первый уровень клана удалить нельзя.');
        }

        $clanLevel->delete();
        $this->refreshClanLevels($clanLevelService);

        return back()->with('success', 'Уровень клана удалён.');
    }

    /** @return array{level: int, experience_required: string} */
    private function validatedData(Request $request, ?ClanLevel $clanLevel = null): array
    {
        $data = $request->validate([
            'level' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique('clan_levels', 'level')->ignore($clanLevel),
            ],
            'experience_required' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999999',
                Rule::unique('clan_levels', 'experience_required')->ignore($clanLevel),
            ],
        ]);

        $level = (int) $data['level'];
        $experienceRequired = (float) $data['experience_required'];

        if ($level === 1 && $experienceRequired !== 0.0) {
            throw ValidationException::withMessages(['experience_required' => 'Для первого уровня требуется 0 опыта.']);
        }

        $this->ensureThresholdOrder($level, $experienceRequired, $clanLevel);

        return [
            'level' => $level,
            'experience_required' => $data['experience_required'],
        ];
    }

    private function ensureThresholdOrder(int $level, float $experienceRequired, ?ClanLevel $current): void
    {
        $levels = ClanLevel::query()
            ->when($current !== null, fn ($query) => $query->whereKeyNot($current->id))
            ->get(['level', 'experience_required']);

        $previous = $levels->where('level', '<', $level)->sortByDesc('level')->first();
        if ($previous !== null && $experienceRequired <= (float) $previous->experience_required) {
            throw ValidationException::withMessages(['experience_required' => 'Порог должен быть больше порога предыдущего уровня.']);
        }

        $next = $levels->where('level', '>', $level)->sortBy('level')->first();
        if ($next !== null && $experienceRequired >= (float) $next->experience_required) {
            throw ValidationException::withMessages(['experience_required' => 'Порог должен быть меньше порога следующего уровня.']);
        }
    }

    private function refreshClanLevels(ClanLevelService $clanLevelService): void
    {
        $clanLevelService->forgetThresholds();
        $clanLevelService->synchronizeAllClans();
    }
}
