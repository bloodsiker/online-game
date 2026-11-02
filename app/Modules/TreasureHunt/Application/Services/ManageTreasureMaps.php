<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Application\Services;

use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Support\Facades\DB;

final class ManageTreasureMaps
{
    public function __construct(private readonly TreasureMapPublicationGuard $publication) {}

    public function save(array $data, ?int $id = null): TreasureMapConfig
    {
        return DB::transaction(function () use ($data, $id): TreasureMapConfig {
            $config = $id ? TreasureMapConfig::whereKey($id)->lockForUpdate()->firstOrFail() : new TreasureMapConfig;
            $this->publication->assertCanReassign($config, (int) $data['share_item_id']);
            $config->fill(collect($data)->except(['map_id', 'locations', 'clue_ru', 'clue_uk'])->all());
            $config->map_id = null;
            $config->setTranslations('clue', ['ru' => $data['clue_ru'], 'uk' => ($data['clue_uk'] ?? '') ?: $data['clue_ru']]);
            $config->save();

            return $config;
        });
    }

    public function saveBonuses(array $bonuses): void
    {
        DB::transaction(function () use ($bonuses): void {
            foreach ($bonuses as $tierId => $bonus) {
                DB::table('treasure_reputation_bonuses')->updateOrInsert(['reputation_tier_id' => $tierId], [
                    ...$bonus, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }
}
