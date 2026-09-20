<?php

declare(strict_types=1);

namespace App\Modules\Forum\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForumSectionController extends Controller
{
    public function index(): View
    {
        $sections = ForumSection::query()
            ->whereNull('parent_id')
            ->withCount('topics')
            ->with(['allChildren' => fn ($query) => $query->with('parent')->withCount('topics')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('forum::admin.sections.index', compact('sections'));
    }

    public function create(): View
    {
        return view('forum::admin.sections.form', [
            'section' => null,
            'parentSections' => $this->parentSectionOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $section = new ForumSection;
        $this->fill($section, $request);
        $section->save();

        return redirect()->route('admin.forum.sections.index')->with('success', 'Категория форума создана.');
    }

    public function edit(ForumSection $section): View
    {
        return view('forum::admin.sections.form', [
            'section' => $section,
            'parentSections' => $this->parentSectionOptions($section),
        ]);
    }

    public function update(Request $request, ForumSection $section): RedirectResponse
    {
        $this->fill($section, $request);
        $section->save();

        return redirect()->route('admin.forum.sections.index')->with('success', 'Категория форума сохранена.');
    }

    public function destroy(ForumSection $section): RedirectResponse
    {
        if ($section->allChildren()->exists()) {
            return redirect()->back()->with('error', 'Сначала удалите вложенные категории.');
        }

        if ($section->topics()->exists()) {
            return redirect()->back()->with('error', 'Нельзя удалить категорию, в которой есть темы.');
        }

        $section->delete();

        return redirect()->route('admin.forum.sections.index')->with('success', 'Категория форума удалена.');
    }

    private function fill(ForumSection $section, Request $request): void
    {
        $data = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('forum_sections', 'id')->where(fn ($query) => $query->whereNull('parent_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('forum_sections', 'slug')->ignore($section->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);

        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        if ($section->exists && $parentId === (int) $section->id) {
            throw ValidationException::withMessages(['parent_id' => 'Категорию нельзя вложить саму в себя.']);
        }

        if ($section->exists && $parentId !== null && $section->allChildren()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Категорию с подразделами нельзя сделать вложенной.',
            ]);
        }

        $section->fill([
            'parent_id' => $parentId,
            'name' => trim($data['name']),
            'slug' => $data['slug'] ?: $this->uniqueSlug($data['name'], $section),
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'allow_topics' => $request->boolean('allow_topics'),
            'allow_comments' => $request->boolean('allow_comments'),
        ]);
    }

    private function uniqueSlug(string $name, ForumSection $section): string
    {
        $base = Str::slug($name) ?: 'section';
        $slug = $base;
        $suffix = 2;

        while (ForumSection::query()
            ->where('slug', $slug)
            ->when($section->exists, fn ($query) => $query->whereKeyNot($section->id))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /** @return Collection<int, ForumSection> */
    private function parentSectionOptions(?ForumSection $excluded = null): Collection
    {
        return ForumSection::query()
            ->whereNull('parent_id')
            ->when($excluded !== null, fn ($query) => $query->whereKeyNot($excluded->id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
