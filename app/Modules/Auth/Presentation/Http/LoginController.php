<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->route('game');
        }

        return back()->withErrors([
            'email' => 'Неправильный логин или пароль.',
        ])->onlyInput('email');
    }
}
