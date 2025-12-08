<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShareAction;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    public function list()
    {
        $listActions = ShareAction::query()->orderByDesc('id')->get();

        return view('admin.action.list', compact('listActions'));
    }

    public function info(Request $request, ShareAction $action)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            return redirect()->back();
        }

        $action->load(['structures']);

        return view('admin.action.info', compact('action'));
    }
}
