<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Presentation\Http\Requests\RegisterRequest;
use App\Modules\Player\Application\UseCases\RegisterPlayerProfile;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Referral\Application\UseCases\ApplyReferralCode;
use App\Modules\User\Domain\Events\UserRegistered;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterPlayerProfile $registerPlayerProfile,
        private readonly ApplyReferralCode $applyReferralCode,
    ) {}

    public function index(Request $request): View
    {
        return view('auth::register', [
            'races' => Race::query()->get(),
            'refCode' => $request->query('ref'),
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'sex' => $request->string('sex')->toString(),
            'location_id' => 1,
            'prev_location_id' => 1,
        ]);

        $this->registerPlayerProfile->execute($user, $request->integer('race'));

        if ($request->filled('ref_code')) {
            $this->applyReferralCode->handle($user, $request->string('ref_code')->toString());
        }

        event(new UserRegistered($user));
        auth()->login($user);

        return redirect()->route('game')->with('success', 'Регистрация прошла успешно!');
    }

    public function registerCheck(Request $request): JsonResponse
    {
        return response()->json([
            'exists' => User::query()->where('name', $request->string('nick')->toString())->exists(),
        ]);
    }
}
