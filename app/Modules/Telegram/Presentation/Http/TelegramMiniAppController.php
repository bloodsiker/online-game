<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Telegram\Application\Services\TelegramProfileService;
use App\Modules\Telegram\Application\Services\TelegramReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\Rule;

final class TelegramMiniAppController
{
    use Localizable;

    public function show(Request $request): Response
    {
        $locale = $request->query('lang', 'ru');
        $locale = in_array($locale, ['ru', 'uk'], true) ? $locale : 'ru';

        return $this->withLocale($locale, fn (): Response => response()->view('telegram::backpack-app', ['labels' => $this->labels()])
            ->withHeaders(['Cache-Control' => 'no-store, private', 'Referrer-Policy' => 'no-referrer',
                'Content-Security-Policy' => "frame-ancestors 'self' https://web.telegram.org https://*.telegram.org; base-uri 'self'"]));
    }

    public function backpack(Request $request, TelegramReadService $read): JsonResponse
    {
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'locale' => app()->getLocale(), 'labels' => $this->labels(),
            'character' => $read->character(userId: $userId),
        ] + $read->allBackpackItems(userId: $userId)));
    }

    public function character(Request $request, TelegramReadService $read, TelegramProfileService $profile): JsonResponse
    {
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'locale' => app()->getLocale(), 'labels' => $this->labels(),
            'character' => $profile->character(userId: $userId),
        ]));
    }

    public function auction(Request $request, TelegramReadService $read): JsonResponse
    {
        $input = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'scope' => ['sometimes', Rule::in(['all', 'mine'])],
            'name' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::enum(ShareItemType::class)],
            'rarity' => ['nullable', Rule::enum(ItemRarity::class)],
        ]);
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'locale' => app()->getLocale(), 'labels' => $this->labels(),
            'character' => $read->character(userId: $userId),
        ] + $read->auction(userId: $userId, scope: $input['scope'] ?? 'all', page: (int) ($input['page'] ?? 1),
            name: trim($input['name'] ?? ''), type: isset($input['type']) ? ShareItemType::from($input['type']) : null,
            rarity: isset($input['rarity']) ? ItemRarity::from($input['rarity']) : null)));
    }

    public function reputations(Request $request, TelegramReadService $read, TelegramProfileService $profile): JsonResponse
    {
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'locale' => app()->getLocale(), 'labels' => $this->labels(),
            'character' => $read->character(userId: $userId), 'data' => $profile->reputations(userId: $userId),
        ]));
    }

    public function influences(Request $request, TelegramReadService $read, TelegramProfileService $profile): JsonResponse
    {
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'locale' => app()->getLocale(), 'labels' => $this->labels(),
            'character' => $read->character(userId: $userId), 'data' => $profile->influences(userId: $userId),
        ]));
    }

    public function onlineCount(Request $request, TelegramProfileService $profile): JsonResponse
    {
        return response()->json(['ok' => true, 'online_count' => $profile->onlineCount(userId: $request->attributes->get('telegram.account')->userId)]);
    }

    public function online(Request $request, TelegramReadService $read, TelegramProfileService $profile): JsonResponse
    {
        $input = $request->validate(['page' => ['sometimes', 'integer', 'min:1', 'max:100000']]);
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'locale' => app()->getLocale(), 'labels' => $this->labels(),
            'character' => $read->character(userId: $userId),
        ] + $profile->onlinePlayers(userId: $userId, page: (int) ($input['page'] ?? 1))));
    }

    /** @return array<string, string> */
    private function labels(): array
    {
        $labels = [];
        foreach (['backpack', 'backpack_empty', 'mini_app_open_from_bot', 'mini_app_session_expired', 'mini_app_loading',
            'mini_app_load_failed', 'mini_app_refresh', 'mini_app_empty', 'mini_app_quantity', 'mini_app_type',
            'mini_app_upgrade', 'mini_app_open', 'previous', 'next', 'account_unavailable', 'not_linked',
            'character', 'coins', 'mini_app_bag', 'mini_app_auction', 'mini_app_navigation', 'mini_app_level',
            'mini_app_money', 'mini_app_diamonds', 'mini_app_own_lots', 'mini_app_no_lots', 'mini_app_lot',
            'mini_app_total_price', 'mini_app_unit_price', 'mini_app_market_page'] as $key) {
            $labels[$key] = __('telegram.'.$key);
        }

        foreach (array_keys(__('telegram')) as $key) {
            if (str_starts_with($key, 'chat_') || str_starts_with($key, 'profile_') || str_starts_with($key, 'auction_')) {
                $labels[$key] = __('telegram.'.$key);
            }
        }

        return $labels + ['close' => __('ui.close'), 'connection_failed' => __('ui.connection_failed'), 'too_many_attempts' => __('ui.too_many_attempts')];
    }
}
