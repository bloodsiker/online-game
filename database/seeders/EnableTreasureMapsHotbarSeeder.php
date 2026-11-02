<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Domain\Enums\ShareItemType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class EnableTreasureMapsHotbarSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $items = DB::table('share_items')->where('type', ShareItemType::TREASURE_MAP->value)
                ->where('is_slot_usable', false)->orderBy('id')->lockForUpdate()->get(['id', 'is_slot_usable']);
            if ($items->isEmpty()) {
                $this->command?->info('Все карты уже доступны для панели быстрого доступа.');

                return;
            }
            $backup = 'backups/treasure-hunt/hotbar-flags-before-'.now()->format('Ymd-His-u').'.json';
            Storage::disk('local')->put($backup, json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            DB::table('share_items')->whereIn('id', $items->pluck('id'))->update(['is_slot_usable' => true, 'updated_at' => now()]);
            $this->command?->info('Карты разрешены для хотбара: '.$items->count().'. Резервная копия: '.$backup);
        });
    }
}
