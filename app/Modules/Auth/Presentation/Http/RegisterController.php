<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Presentation\Http\Requests\RegisterRequest;
use App\Modules\Player\Application\UseCases\RegisterPlayerProfile;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Race\Infrastructure\Persistence\Models\RaceAvatar;
use App\Modules\Referral\Application\UseCases\ApplyReferralCode;
use App\Modules\User\Domain\Events\UserRegistered;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterPlayerProfile $registerPlayerProfile,
        private readonly ApplyReferralCode $applyReferralCode,
    ) {}

    public function index(Request $request): View
    {
        $avatarCatalogReady = Schema::hasTable('race_avatars');

        return view('auth::register', [
            'races' => Race::query()->when($avatarCatalogReady, fn ($query) => $query->with('avatars'))->get(),
            'refCode' => $request->query('ref'),
            'onlineCount' => User::query()
                ->where('last_online_at', '>=', now()->subMinutes((int) config('game.online_timeout_minutes', 30)))
                ->count(),
            'avatarCatalogReady' => $avatarCatalogReady,
            'registrationAvatars' => config('registration_avatars'),
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

        $avatarPath = Schema::hasTable('race_avatars')
            ? RaceAvatar::query()
                ->where('race_id', $request->integer('race'))
                ->where('sex', $request->string('sex')->toString())
                ->findOrFail($request->integer('avatar'))
                ->image_url
            : asset(config('registration_avatars.'.(string) $request->string('sex').'.'.(string) $request->string('avatar').'.image'));
        $user->profile()->create([
            'sex' => $request->string('sex')->toString(),
            'avatar' => $avatarPath,
        ]);

        if ($request->filled('ref_code')) {
            $this->applyReferralCode->handle($user, $request->string('ref_code')->toString());
        }

        event(new UserRegistered($user));
        $verificationEmailSent = true;

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);
            $verificationEmailSent = false;
        }

        auth()->login($user);

        return redirect()->route('verification.notice')->with(
            'status',
            $verificationEmailSent ? 'verification-link-sent' : 'verification-email-failed',
        );
    }

    public function registerCheck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nick' => ['required', 'string', 'min:2', 'max:16'],
        ]);
        $nick = $validated['nick'];

        if (! User::query()->where('name', $nick)->exists()) {
            return response()->json(['exists' => false, 'suggestions' => [[], [], []]]);
        }

        $candidates = [
            [$nick.'1', $nick.'2', $nick.'3'],
            ['-'.$nick.'-', '-'.$nick, $nick.'-'],
            ['_'.$nick.'_', '_'.$nick, $nick.'_'],
        ];
        $allCandidates = collect($candidates)
            ->flatten()
            ->filter(fn (string $candidate): bool => mb_strlen($candidate) <= 16)
            ->unique()
            ->values();
        $taken = User::query()
            ->whereIn('name', $allCandidates)
            ->pluck('name')
            ->all();
        $taken = array_fill_keys($taken, true);
        $suggestions = array_map(
            fn (array $group): array => array_values(array_filter(
                $group,
                fn (string $candidate): bool => mb_strlen($candidate) <= 16 && ! isset($taken[$candidate]),
            )),
            $candidates,
        );

        return response()->json(['exists' => true, 'suggestions' => $suggestions]);
    }
}
