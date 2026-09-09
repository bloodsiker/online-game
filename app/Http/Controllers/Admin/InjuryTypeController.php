<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Player\Application\Requests\Admin\SaveInjuryTypeRequest;
use App\Modules\Player\Application\UseCases\Admin\DeleteInjuryType;
use App\Modules\Player\Application\UseCases\Admin\SaveInjuryType;
use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Enums\InjurySeverity;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InjuryTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $bodyPart = InjuryBodyPart::tryFrom((string) $request->query('body_part', ''));

        $injuryTypes = InjuryType::query()
            ->withCount('playerInjuries')
            ->when($query !== '', function ($builder) use ($query): void {
                $search = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $query).'%';
                $builder->where(fn ($builder) => $builder
                    ->where('name', 'like', $search)
                    ->orWhere('slug', 'like', $search));
            })
            ->when($bodyPart !== null, fn ($builder) => $builder->where('body_part', $bodyPart->value))
            ->orderBy('body_part')
            ->orderBy('severity')
            ->orderBy('id')
            ->get();

        return view('admin.injury_type.index', [
            'injuryTypes' => $injuryTypes,
            'bodyParts' => InjuryBodyPart::cases(),
            'filters' => ['q' => $query, 'body_part' => $bodyPart?->value ?? ''],
        ]);
    }

    public function create(): View
    {
        return $this->formView(new InjuryType);
    }

    public function store(SaveInjuryTypeRequest $request, SaveInjuryType $save): RedirectResponse
    {
        $injuryType = $save->execute(
            new InjuryType,
            $request->validated(),
            $request->file('image'),
            $request->boolean('delete_image'),
        );

        return redirect()->route('admin.injury_type.edit', $injuryType)
            ->with('success', 'Травма создана.');
    }

    public function edit(InjuryType $injuryType): View
    {
        return $this->formView($injuryType);
    }

    public function update(
        SaveInjuryTypeRequest $request,
        InjuryType $injuryType,
        SaveInjuryType $save,
    ): RedirectResponse {
        $save->execute(
            $injuryType,
            $request->validated(),
            $request->file('image'),
            $request->boolean('delete_image'),
        );

        return redirect()->back()->with('success', 'Травма сохранена.');
    }

    public function destroy(InjuryType $injuryType, DeleteInjuryType $delete): RedirectResponse
    {
        if (! $delete->execute($injuryType)) {
            return redirect()->back()->withErrors([
                'injury' => 'Нельзя удалить травму, которая уже была выдана игроку. Отключите её.',
            ]);
        }

        return redirect()->route('admin.injury_types')->with('success', 'Травма удалена.');
    }

    private function formView(InjuryType $injuryType): View
    {
        return view('admin.injury_type.form', [
            'injuryType' => $injuryType,
            'bodyParts' => InjuryBodyPart::cases(),
            'severities' => InjurySeverity::cases(),
            'statKeys' => PlayerStatKey::cases(),
        ]);
    }
}
