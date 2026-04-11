<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function dungeon(): View
    {
        return view('admin.docs.dungeon');
    }

    public function clan(): View
    {
        return view('admin.docs.clan');
    }
}