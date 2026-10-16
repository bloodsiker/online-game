<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActionController extends Controller
{
    public function list(): View
    {
        $listActions = ShareAction::orderByDesc('id')->get();

        return view('admin.action.list', compact('listActions'));
    }

    public function create(Request $request, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $data = $request->validate(['alias' => ['required', 'string', 'max:255']]);
            $action = new ShareAction;
            $translationEditor->fill($action, $request, ['name']);
            $action->alias = $data['alias'];
            $action->save();

            return redirect()->route('admin.action.info', $action->id)->with('success', 'Действие создано.');
        }

        return view('admin.action.create');
    }

    public function info(Request $request, ShareAction $action, ContentTranslationEditor $translationEditor): mixed
    {
        if ($request->isMethod('POST')) {
            $data = $request->validate(['alias' => ['required', 'string', 'max:255']]);
            $translationEditor->fill($action, $request, ['name']);
            $action->alias = $data['alias'];
            $action->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $action->load(['structures']);

        return view('admin.action.info', compact('action'));
    }
}
