<?php

declare(strict_types=1);

namespace App\Modules\Trade\Application\Services;

use App\Modules\Share\Domain\Enums\ShareItemType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeAdminService
{
    public function save(string $section, array $data): void
    {
        DB::transaction(function () use ($section, $data): void {
            $id = $data['id'] ?? null;
            unset($data['id']);
            $table = match ($section) {
                'posts' => 'trade_posts', 'goods' => 'trade_goods', 'routes' => 'trade_routes',
                'offers' => 'trade_buy_offers', 'stocks' => 'trade_post_stocks',
            };
            $existing = $id ? DB::table($table)->where('id', $id)->lockForUpdate()->first() : null;
            abort_if($id && ! $existing, 404);
            if ($section === 'goods') {
                $item = DB::table('share_items')->where('id', $data['share_item_id'])->first();
                $this->check($item && $item->type === ShareItemType::TRADE_GOODS->value, 'Выберите предмет типа «Торговый товар».');
                $this->check(! DB::table($table)->where('share_item_id', $data['share_item_id'])->when($id, fn ($q) => $q->where('id', '!=', $id))->exists(), 'У товара уже есть продавец.');
                if ($existing) {
                    $this->check((int) $existing->share_item_id === (int) $data['share_item_id']
                        && (int) $existing->trade_post_id === (int) $data['trade_post_id']
                        && (int) $existing->base_price === (int) $data['base_price'],
                        'Для изменения продавца, предмета или закупочной цены создайте новый товар: старый груз может быть у игроков.');
                }
            }
            if ($section === 'routes') {
                $a = (int) $data['trade_post_a_id'];
                $b = (int) $data['trade_post_b_id'];
                $this->check($a !== $b, 'Выберите разных торговцев.');
                $data['trade_post_a_id'] = min($a, $b);
                $data['trade_post_b_id'] = max($a, $b);
                $unique = ['trade_post_a_id', 'trade_post_b_id'];
            } elseif ($section === 'offers') {
                $good = DB::table('trade_goods')->where('id', $data['trade_good_id'])->first();
                $this->check($good && (int) $good->trade_post_id !== (int) $data['trade_post_id'], 'Торговец не принимает собственный товар.');
                $this->check((int) $data['buy_price'] >= (int) $good->base_price, 'Цена приёма не может быть ниже закупочной.');
                $unique = ['trade_post_id', 'trade_good_id'];
            } elseif ($section === 'posts') {
                $unique = ['npc_id'];
            } elseif ($section === 'stocks') {
                $this->check($data['current_quantity'] <= $data['max_quantity'], 'Запас не может превышать максимум.');
                $unique = ['trade_good_id'];
                $data['last_restock_at'] = now();
            }
            if (isset($unique)) {
                $query = DB::table($table);
                foreach ($unique as $column) {
                    $query->where($column, $data[$column]);
                }
                $this->check(! $query->when($id, fn ($q) => $q->where('id', '!=', $id))->exists(), 'Такая запись уже существует.');
            }
            $data['updated_at'] = now();
            if ($existing) {
                DB::table($table)->where('id', $id)->update($data);
            } else {
                $data['created_at'] = now();
                $newId = DB::table($table)->insertGetId($data);
                if ($section === 'goods') {
                    DB::table('trade_post_stocks')->insert([
                        'trade_good_id' => $newId, 'last_restock_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['trade' => $message]);
        }
    }
}
