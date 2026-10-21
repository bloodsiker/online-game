<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\ItemAssembly\Application\Requests\SaveItemAssemblyRequest;
use App\Modules\ItemAssembly\Application\Services\ManageItemAssembly;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemAssemblyController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['type' => ['nullable', Rule::enum(AssemblyType::class)], 'q' => ['nullable', 'string', 'max:100']]);
        $assemblies = ItemAssembly::query()->withCount(['components', 'rewards'])
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('q'), fn ($query) => $query->searchTranslations('%'.$request->input('q').'%'))
            ->orderBy('sort_order')->orderBy('id')->paginate(30)->withQueryString();

        return view('admin.item_assembly.index', ['assemblies' => $assemblies, 'types' => AssemblyType::cases()]);
    }

    public function create(): View
    {
        return $this->form(new ItemAssembly);
    }

    public function edit(ItemAssembly $assembly): View
    {
        return $this->form($assembly->load(['components', 'rewards']));
    }

    public function store(SaveItemAssemblyRequest $request, ManageItemAssembly $manager): RedirectResponse
    {
        $assembly = $manager->save(new ItemAssembly, $request->validated());

        return redirect()->route('admin.item_assemblies.edit', $assembly)->with('success', 'Сборка создана.');
    }

    public function update(SaveItemAssemblyRequest $request, ItemAssembly $assembly, ManageItemAssembly $manager): RedirectResponse
    {
        $manager->save($assembly, $request->validated());

        return redirect()->route('admin.item_assemblies.edit', $assembly)->with('success', 'Сборка сохранена.');
    }

    private function form(ItemAssembly $assembly): View
    {
        return view('admin.item_assembly.form', ['assembly' => $assembly, 'types' => AssemblyType::cases(), 'modes' => AssemblyResultMode::cases()]);
    }
}
