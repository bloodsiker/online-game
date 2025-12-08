<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Factories\PlayerFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Race;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index()
    {
        $races = Race::all();

        return view('auth.register', compact('races'));
    }

    public function register(RegisterRequest $request, PlayerFactory $playerFactory)
    {
        $user = User::create([
            'name' => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'sex'      => $request->sex,
            'location_id' => 1,
            'prev_location_id' => 1,
        ]);

        $playerFactory->create($user, $request->race);

        event(new UserRegistered($user));

        auth()->login($user);
//        $user->sendEmailVerificationNotification();

        return redirect()->route('game')->with('success', 'Регистрация прошла успешно!');

//        return redirect()->route('verification.notice')
//            ->with('success', 'На вашу почту отправлено письмо для подтверждения регистрации.');
    }

    public function registerCheck(Request $request)
    {
        $user = User::where('name', $request->get('nick'))->first();

        return response()->json(['exists' => $user ? true : false]);
    }
}
