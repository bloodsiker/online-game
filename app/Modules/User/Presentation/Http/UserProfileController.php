<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http;

use App\Modules\User\Application\Services\UserProfileService;
use App\Modules\User\Infrastructure\Persistence\Models\UserProfile;
use App\Modules\User\Presentation\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class UserProfileController
{
    public function show(Request $request): View
    {
        return view('interface::settings', [
            'tab' => 'profile',
            'profile' => UserProfile::query()->where('user_id', $request->user()->id)->first(),
        ]);
    }

    public function update(UpdateProfileRequest $request, UserProfileService $service): RedirectResponse
    {
        $service->save(userId: $request->user()->id, data: $request->validated());

        return redirect()->route('settings.profile')->with('message', 'Анкета сохранена.');
    }
}
