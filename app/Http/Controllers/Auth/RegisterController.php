<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Modules\Player\Application\UseCases\RegisterPlayerProfile;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Referral\Application\UseCases\ApplyReferralCode;
use App\Modules\User\Infrastructure\Persistence\Models\User;
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

    public function register(RegisterRequest $request, RegisterPlayerProfile $registerPlayerProfile, ApplyReferralCode $applyReferralCode)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sex' => $request->sex,
            'location_id' => 1,
            'prev_location_id' => 1,
        ]);

        $registerPlayerProfile->execute($user, (int) $request->race);

        if ($request->filled('ref_code')) {
            $applyReferralCode->handle($user, $request->input('ref_code'));
        }

        event(new UserRegistered($user));

        auth()->login($user);

        return redirect()->route('game')->with('success', 'Регистрация прошла успешно!');
    }

    public function registerCheck(Request $request)
    {
        $user = User::where('name', $request->get('nick'))->first();

        return response()->json(['exists' => (bool) $user]);
    }
}
