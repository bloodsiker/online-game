<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Factories\PlayerFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Race;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        $races = Race::all();
        $refCode = $request->query('ref');

        return view('auth.register', compact('races', 'refCode'));
    }

    public function register(RegisterRequest $request, PlayerFactory $playerFactory, ReferralService $referralService)
    {
        $user = User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            'sex'              => $request->sex,
            'location_id'      => 1,
            'prev_location_id' => 1,
        ]);

        $playerFactory->create($user, $request->race);

        if ($request->filled('ref_code')) {
            $referralService->applyCode($user, $request->input('ref_code'));
        }

        event(new UserRegistered($user));

        auth()->login($user);

        return redirect()->route('game')->with('success', 'Регистрация прошла успешно!');
    }

    public function registerCheck(Request $request)
    {
        $user = User::where('name', $request->get('nick'))->first();

        return response()->json(['exists' => (bool)$user]);
    }
}