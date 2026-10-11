<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Services\ChangePasswordService;
use App\Modules\Auth\Presentation\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class ChangePasswordController extends Controller
{
    public function __invoke(ChangePasswordRequest $request, ChangePasswordService $service): JsonResponse
    {
        $data = $request->validated();
        $user = $service->execute(
            user: $request->user(),
            currentPassword: $data['current_password'],
            newPassword: $data['password'],
        );

        Auth::guard('web')->setUser($user);
        // Меняем ID сессии, не инвалидируя CSRF в остальных игровых фреймах.
        $request->session()->migrate(true);
        $request->session()->put('password_hash_web', $user->password);

        return response()->json(['ok' => true, 'message' => 'Пароль успешно изменён.']);
    }
}
