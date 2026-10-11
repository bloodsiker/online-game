<?php

declare(strict_types=1);

namespace App\Modules\Interface\Presentation\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ErrorController extends Controller
{
    public function index(Request $request): View
    {
        $message = $request->get('message');

        return view('interface::error', compact('message'));
    }
}
