<?php

declare(strict_types=1);

namespace App\Modules\Festival\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Festival\Application\Services\FestivalService;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FestivalController extends Controller
{
    public function __construct(
        private readonly FestivalService $festival,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function index(Request $request, int $npc): View
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($this->festival->atNpc($user, $npc), 403);
        $page = $this->festival->pageData($user);

        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy(
            $page['rates']->pluck('item')
                ->push($page['settings']?->coin)
                ->filter()
                ->unique('id')
                ->values()
        ));

        return view('festival::index', [
            'npcId' => $npc,
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
            ...$page,
        ]);
    }

    public function rules(Request $request, int $npc): View
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($this->festival->atNpc($user, $npc), 403);

        return view('festival::rules', ['npcId' => $npc, 'rates' => $this->festival->stoneRates()]);
    }

    public function register(Request $request, int $npc): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['side' => ['required', 'string', 'max:8']]);
        $this->festival->register($this->user($request), $npc, (string) $data['side']);

        return $this->back($npc, __('festival.registered'));
    }

    public function deposit(Request $request, int $npc): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['share_item_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'min:1']]);
        $result = $this->festival->deposit($this->user($request), $npc, (int) $data['share_item_id'], (int) $data['quantity']);
        $message = __('festival.deposited', ['points' => $result['points']]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'modal' => __('festival.deposit_modal', [
                    'image' => $this->itemImageHtml($result['item']),
                    'name' => e($result['item']->name),
                    'quantity' => $result['quantity'],
                    'points' => $result['points'],
                    'total' => $result['total'],
                ]),
            ]);
        }

        return $this->back($npc, $message);
    }

    public function claim(Request $request, int $npc): RedirectResponse|JsonResponse
    {
        $coins = $this->festival->claim($this->user($request), $npc);

        return $this->back($npc, __('festival.claimed', ['coins' => $coins]));
    }

    public function exchange(Request $request, int $npc): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['share_item_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'min:1']]);
        $result = $this->festival->exchange($this->user($request), $npc, (int) $data['share_item_id'], (int) $data['quantity']);
        $message = __('festival.exchanged', ['coins' => $result['coins']]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'modal' => __('festival.exchange_modal', [
                    'image' => $this->itemImageHtml($result['item']),
                    'name' => e($result['item']->name),
                    'quantity' => $result['quantity'],
                    'coins' => $result['coins'],
                    'coin' => e($result['coin']->name),
                    'total' => $result['total'],
                ]),
            ]);
        }

        return $this->back($npc, $message);
    }

    /** Иконка предмета в игровой рамке для окна сообщений (оно не подключает стили страницы). */
    private function itemImageHtml(ShareItem $item): string
    {
        return sprintf(
            '<span style="display:inline-block;width:60px;height:60px;padding:5px 6px 6px;background:url(%s) no-repeat;"><img src="%s" width="60" height="60" alt="%s" style="display:block;object-fit:contain;"></span>',
            e(asset('main/images/user-reward-frame.png')),
            e((string) $item->image),
            e($item->name),
        );
    }

    private function user(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }

    private function back(int $npc, string $message): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->route('festival.index', ['npc' => $npc])->with('message', $message);
    }
}
