<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function info(int $id): Response
    {
        $user = User::with(['player.race', 'player.playerEquip', 'player.skills', 'clanMembership.clan', 'clanMembership.role', 'currentLocation.map'])->findOrFail($id);

        $isOnline = $user->last_online_at !== null
            && Carbon::parse($user->last_online_at)->gt(Carbon::now()->subMinutes(10));

        return response()->view('user::info', [
            'user' => $user,
            'age' => $this->formatAge($user->created_at),
            'isOnline' => $isOnline,
            'locationPath' => $this->buildLocationPath($user),
        ]);
    }

    private function buildLocationPath(User $user): string
    {
        $location = $user->currentLocation;
        if ($location === null) {
            return '';
        }

        $path = [];
        $map = $location->map;
        while ($map !== null) {
            array_unshift($path, $map->name);
            $map = $map->parent;
        }
        $path[] = $location->name.' ('.$location->id.')';

        return implode(' / ', $path);
    }

    private function formatAge(?Carbon $createdAt): string
    {
        if ($createdAt === null) {
            return '—';
        }

        $diff = $createdAt->diff(Carbon::now());

        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y.' Год';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m.' Мес.';
        }
        $parts[] = $diff->d.' Дн.';

        return implode(' ', $parts);
    }

    public function logout(Request $request): Response
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response("<script>window.top.location.href='".route('index')."';</script>");
    }
}
