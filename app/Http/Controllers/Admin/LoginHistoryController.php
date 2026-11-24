<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Infrastructure\Persistence\Models\LoginHistory;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginHistoryController extends Controller
{
    private const DEVICE_TYPES = ['desktop' => 'Компьютер', 'mobile' => 'Телефон', 'tablet' => 'Планшет', 'bot' => 'Бот', 'unknown' => 'Неизвестно'];

    public function index(Request $request): View
    {
        $filters = [
            'user' => trim((string) $request->query('user', '')),
            'ip' => trim((string) $request->query('ip', '')),
            'country' => strtoupper(trim((string) $request->query('country', ''))),
            'device_type' => (string) $request->query('device_type', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        $logs = LoginHistory::query()
            ->with('user:id,name,player_id')
            ->when($filters['user'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->whereIn('user_id', User::query()
                        ->where('name', 'like', '%'.$filters['user'].'%')->select('id'));
                    if (ctype_digit($filters['user'])) {
                        $nested->orWhere('user_id', (int) $filters['user']);
                    }
                });
            })
            ->when($filters['ip'] !== '', fn ($query) => $query->where('ip_address', 'like', $filters['ip'].'%'))
            ->when($filters['country'] !== '', fn ($query) => $query->where('country_code', substr($filters['country'], 0, 2)))
            ->when(isset(self::DEVICE_TYPES[$filters['device_type']]), fn ($query) => $query->where('device_type', $filters['device_type']))
            ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.login_history.index', [
            'logs' => $logs,
            'filters' => $filters,
            'deviceTypes' => self::DEVICE_TYPES,
        ]);
    }
}
