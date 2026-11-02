<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdateTreasureMapReputationThresholdsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $thresholds = ['mysterious' => 1000, 'forgotten' => 2000, 'legendary' => 3000, 'heroic' => 5000];
            $configs = DB::table('treasure_map_configs')->whereIn('code', array_keys($thresholds))->orderBy('id')->lockForUpdate()->get();
            if ($configs->count() !== count($thresholds)) {
                throw new \RuntimeException('Не найдены все четыре карты для изменения порогов.');
            }
            $changes = [];
            foreach ($configs as $config) {
                $fields = ['required_reputation' => $thresholds[$config->code]];
                if ($config->code === 'heroic') {
                    $fields['search_duration_seconds'] = 182;
                }
                if (collect($fields)->contains(fn ($value, $field) => (int) $config->$field !== $value)) {
                    $changes[$config->id] = $fields;
                }
            }
            if ($changes !== []) {
                $backup = 'backups/treasure-hunt/thresholds-before-'.now()->format('Ymd-His-u').'.json';
                Storage::disk('local')->put($backup, json_encode($configs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
                foreach ($changes as $id => $fields) {
                    DB::table('treasure_map_configs')->where('id', $id)->update([...$fields, 'updated_at' => now()]);
                }
                $this->command?->info('Пороги обновлены; время поиска героической карты — 182 с. Резервная копия: '.$backup);
            }
            $article = app(LibraryTreasureHunterSeeder::class)->refreshCatalogTables();
            $this->command?->info('Таблицы статьи проверены: #'.$article['article_id']);
        });
    }
}
