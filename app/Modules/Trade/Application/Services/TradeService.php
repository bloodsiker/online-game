<?php

declare(strict_types=1);

namespace App\Modules\Trade\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Influence\Domain\Services\MapInfluenceRequirementService;
use App\Modules\Player\Domain\Services\PeacefulProfessionExperienceService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Trade\Domain\Services\TradeBalance;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeService
{
    public function __construct(
        private readonly BackpackService $backpack,
        private readonly PeacefulProfessionExperienceService $experience,
        private readonly MapInfluenceRequirementService $influenceRequirements,
    ) {}

    public function page(User $user, int $postId): array
    {
        $post = $this->accessiblePost($user, $postId);
        $playerSkill = PlayerSkill::query()->where('player_id', $user->player_id)
            ->whereHas('skill', fn ($query) => $query->where('name', TradeBalance::SKILL_NAME))->first();
        $level = max(1, min(300, (int) ($playerSkill?->lvl ?? 1)));
        $experienceNeeded = max(1, (int) ($playerSkill?->exp_diff ?? 100));
        $levelStart = (int) ($playerSkill?->exp_up ?? 100) - $experienceNeeded;
        $experienceInLevel = max(0, min($experienceNeeded, (int) ($playerSkill?->exp ?? 0) - $levelStart));
        $skillProgress = $level >= 300 ? 100 : round($experienceInLevel * 100 / $experienceNeeded, 2);
        $goods = DB::table('trade_goods')->where('trade_post_id', $postId)->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')->get();
        foreach ($goods as $good) {
            $good->stock = DB::transaction(fn () => $this->stock((int) $good->id));
        }
        $offers = DB::table('trade_buy_offers as offers')
            ->join('trade_goods as goods', 'goods.id', '=', 'offers.trade_good_id')
            ->where('offers.trade_post_id', $postId)->where('offers.is_active', true)
            ->where('goods.is_active', true)
            ->select('offers.*', 'goods.share_item_id', 'goods.base_price', 'goods.trade_post_id as source_id')->get()
            ->filter(fn ($offer) => $this->route((int) $offer->source_id, $postId) !== null)->values();
        foreach ($offers as $offer) {
            $offer->sale_price = TradeBalance::salePrice($offer->base_price, $offer->buy_price, $level);
            $offer->exp_per_unit = $this->route((int) $offer->source_id, $postId)->exp_per_unit;
        }
        $items = ShareItem::query()->whereIn('id', $goods->pluck('share_item_id')->merge($offers->pluck('share_item_id')))
            ->where('type', ShareItemType::TRADE_GOODS->value)->where('is_active', true)->get()->keyBy('id');

        return [
            'post' => $post,
            'level' => $level,
            'skillProgress' => $skillProgress,
            'skillProgressTitle' => $level >= 300 ? 'Максимальный уровень навыка' : 'Опыт: '.$experienceInLevel.' / '.$experienceNeeded,
            'capacity' => TradeBalance::capacity($level),
            'cargoCount' => $this->cargoCount($user),
            'licensed' => DB::table('player_trade_licenses')->where('player_id', $user->player_id)
                ->where('trade_post_id', $postId)->exists(),
            'goods' => $goods,
            'offers' => $offers,
            'items' => $items,
            'counts' => $this->backpack->countByShareItemIds($user, $items->keys()->all()),
            'money' => (int) $user->money,
        ];
    }

    public function act(User $user, int $postId, string $action, ?int $goodId, int $quantity, string $key): array
    {
        return DB::transaction(function () use ($user, $postId, $action, $goodId, $quantity, $key): array {
            $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();
            $user = User::query()->without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $user->setRelation('player', $player);
            $previous = DB::table('trade_logs')->where('player_id', $player->id)->where('request_key', $key)->first();
            if ($previous !== null) {
                $this->require((int) $previous->trade_post_id === $postId && $previous->action === $action
                    && (int) $previous->trade_good_id === (int) $goodId && (int) $previous->quantity === $quantity,
                    'Ключ запроса уже использован для другого действия.');

                return ['message' => $previous->message, 'money' => (int) $user->money];
            }
            $post = $this->accessiblePost($user, $postId, true);
            $this->require($player->hp_now > 0, 'Торговля недоступна погибшему персонажу.');
            $this->require(! DB::table('battle_details as details')
                ->join('battles', 'battles.id', '=', 'details.battle_id')
                ->where('details.user_id', $user->id)->where('details.status', 1)
                ->where('battles.status', 1)->where('battles.location_id', $user->location_id)->exists(),
                'Завершите бой перед торговлей.');
            $level = $this->skillLevel((int) $player->id);
            $licensed = DB::table('player_trade_licenses')->where('player_id', $player->id)->where('trade_post_id', $postId)->exists();
            $experience = 0;

            if ($action === 'license') {
                $this->require(! $licensed, 'Лицензия уже приобретена.');
                $amount = (int) $post->license_price;
                $this->require($user->money >= $amount, 'Недостаточно монет.');
                $user->decrement('money', $amount);
                DB::table('player_trade_licenses')->insert([
                    'player_id' => $player->id, 'trade_post_id' => $postId, 'purchased_at' => now(),
                ]);
                $skill = Skill::query()->where('name', TradeBalance::SKILL_NAME)->firstOrFail();
                PlayerSkill::query()->firstOrCreate(['player_id' => $player->id, 'skill_id' => $skill->id], [
                    'lvl' => 1, 'exp' => 0, 'exp_up' => TradeBalance::levelThreshold(1), 'exp_diff' => 100,
                ]);
                $message = 'Приобретена лицензия на торговлю с «'.$post->name.'».';
            } else {
                $this->require($licensed, 'Сначала приобретите лицензию этого торговца.');
                $this->require($quantity > 0 && $quantity <= 10000, 'Неверное количество товара.');
                $good = DB::table('trade_goods')->where('id', $goodId)->where('is_active', true)->lockForUpdate()->first();
                $this->require($good !== null, 'Товар недоступен.');
                $item = ShareItem::query()->whereKey($good->share_item_id)->lockForUpdate()->firstOrFail();
                $this->require($item->type === ShareItemType::TRADE_GOODS && $item->is_active, 'Товар недоступен.');

                if ($action === 'purchase') {
                    $this->require((int) $good->trade_post_id === $postId, 'Этот торговец не продаёт выбранный товар.');
                    $this->require($level >= $good->required_skill_lvl, 'Недостаточный уровень навыка «Вольный торг».');
                    $this->require($this->cargoCount($user) + $quantity <= TradeBalance::capacity($level),
                        'Превышен лимит перевозки торговых товаров.');
                    $stock = $this->stock((int) $good->id);
                    $this->require($stock->current_quantity >= $quantity, 'Товар уже раскуплен другими игроками.');
                    $amount = (int) $good->base_price * $quantity;
                    $this->require($user->money >= $amount, 'Недостаточно монет.');
                    $user->decrement('money', $amount);
                    DB::table('trade_post_stocks')->where('id', $stock->id)->decrement('current_quantity', $quantity);
                    $this->backpack->addItemByShareItem($user, $item, $quantity);
                    $message = 'Куплено: '.$item->name.' ×'.$quantity.'. Потрачено '.$amount.' монет.';
                } else {
                    $this->require($action === 'sale', 'Неизвестное действие.');
                    $this->require((int) $good->trade_post_id !== $postId, 'Торговец не принимает собственные товары.');
                    $offer = DB::table('trade_buy_offers')->where('trade_post_id', $postId)
                        ->where('trade_good_id', $goodId)->where('is_active', true)->lockForUpdate()->first();
                    $route = $this->route((int) $good->trade_post_id, $postId);
                    $this->require($offer !== null && $route !== null, 'Торговец не принимает этот товар.');
                    $this->require($offer->buy_price >= $good->base_price, 'Цена скупки настроена неверно.');
                    $rows = Backpack::query()->where('user_id', $user->id)->where('equipped', 0)
                        ->whereHas('item', fn ($query) => $query->where('share_item_id', $item->id))
                        ->orderBy('id')->lockForUpdate()->get();
                    $this->require($rows->sum('count') >= $quantity, 'В рюкзаке недостаточно товара.');
                    $remaining = $quantity;
                    foreach ($rows as $row) {
                        $take = min($remaining, (int) $row->count);
                        if ($take === 0) {
                            break;
                        }
                        if ($take === (int) $row->count) {
                            $instance = $row->item;
                            $row->delete();
                            $instance->delete();
                        } else {
                            $row->decrement('count', $take);
                        }
                        $remaining -= $take;
                    }
                    $amount = TradeBalance::salePrice($good->base_price, $offer->buy_price, $level) * $quantity;
                    $user->increment('money', $amount);
                    $experience = (int) $route->exp_per_unit * $quantity;
                    $skill = Skill::query()->where('name', TradeBalance::SKILL_NAME)->firstOrFail();
                    if ($experience > 0) {
                        $this->experience->award($player, $skill, $experience);
                    }
                    $message = 'Продано: '.$item->name.' ×'.$quantity.'. Получено '.$amount.' монет и '.$experience.' опыта «Вольный торг».';
                }
            }
            DB::table('trade_logs')->insert([
                'player_id' => $player->id, 'trade_post_id' => $postId, 'trade_good_id' => $goodId,
                'request_key' => $key, 'action' => $action, 'quantity' => $quantity,
                'amount' => $amount, 'experience' => $experience, 'skill_level' => $level,
                'message' => $message, 'created_at' => now(), 'updated_at' => now(),
            ]);

            return ['message' => $message, 'money' => (int) $user->money];
        }, attempts: 3);
    }

    private function accessiblePost(User $user, int $id, bool $lock = false): object
    {
        $query = DB::table('trade_posts as posts')->join('npcs', 'npcs.id', '=', 'posts.npc_id')
            ->where('posts.id', $id)->where('posts.is_active', true)->where('npcs.is_active', true)
            ->select('posts.*', 'npcs.location_id');
        $post = ($lock ? $query->lockForUpdate() : $query)->first();
        abort_if($post === null, 404);
        $this->require((int) $user->location_id === (int) $post->location_id, 'Торговец находится на другой локации.');
        $this->require($this->influenceRequirements->allowsNpc((int) $user->id, (int) $post->npc_id),
            'Недостаточно влияния для торговли с этим персонажем.');

        return $post;
    }

    private function skillLevel(int $playerId): int
    {
        $skillId = Skill::query()->where('name', TradeBalance::SKILL_NAME)->value('id');

        return max(1, min(300, (int) PlayerSkill::query()->where('player_id', $playerId)
            ->where('skill_id', $skillId)->value('lvl')));
    }

    private function cargoCount(User $user): int
    {
        return (int) DB::table('backpacks')->join('items', 'items.id', '=', 'backpacks.item_id')
            ->join('share_items', 'share_items.id', '=', 'items.share_item_id')
            ->where('backpacks.user_id', $user->id)->where('share_items.type', ShareItemType::TRADE_GOODS->value)
            ->sum('backpacks.count');
    }

    private function route(int $source, int $destination): ?object
    {
        return DB::table('trade_routes')->where('trade_post_a_id', min($source, $destination))
            ->where('trade_post_b_id', max($source, $destination))->where('is_active', true)->first();
    }

    private function stock(int $goodId): object
    {
        $stock = DB::table('trade_post_stocks')->where('trade_good_id', $goodId)->lockForUpdate()->first();
        $this->require($stock !== null, 'Запас товара не настроен.');
        $seconds = max(1, (int) $stock->restock_interval_minutes) * 60;
        $last = Carbon::parse($stock->last_restock_at);
        $intervals = intdiv(max(0, now()->timestamp - $last->timestamp), $seconds);
        if ($intervals > 0) {
            $stock->current_quantity = min($stock->max_quantity, $stock->current_quantity + $intervals * $stock->restock_amount);
            DB::table('trade_post_stocks')->where('id', $stock->id)->update([
                'current_quantity' => $stock->current_quantity,
                'last_restock_at' => $last->addSeconds($intervals * $seconds), 'updated_at' => now(),
            ]);
        }

        return $stock;
    }

    private function require(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['trade' => $message]);
        }
    }
}
