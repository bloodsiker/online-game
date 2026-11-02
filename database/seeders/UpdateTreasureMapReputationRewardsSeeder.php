<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class UpdateTreasureMapReputationRewardsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $configs = DB::table('treasure_map_configs')->orderBy('id')->lockForUpdate()->get();
            $article = LibraryArticle::where('slug', 'kladoiskatel')->lockForUpdate()->first();
            $translations = $article?->getTranslations('content') ?? [];
            $updated = [];
            foreach ($translations as $locale => $content) {
                $updated[$locale] = preg_replace_callback(
                    '~(<th\b[^>]*>\s*(?:Репутация за находку|Репутація за знахідку)\s*</th>\s*</tr>\s*</thead>\s*<tbody>)(.*?)(</tbody>)~su',
                    static fn ($table) => $table[1].preg_replace_callback(
                        '~(<td\b[^>]*>)\+\d+(</td>\s*</tr>)~u',
                        static fn ($cell) => $cell[1].'+10'.$cell[2],
                        $table[2],
                    ).$table[3],
                    $content,
                );
            }
            if ($configs->every(fn ($config) => (int) $config->reputation_reward === 10) && $translations === $updated) {
                $this->command?->info('Все карты уже дают по 10 репутации; изменений нет.');

                return;
            }
            $backup = 'backups/treasure-hunt/reputation-rewards-before-'.now()->format('Ymd-His-u').'.json';
            Storage::disk('local')->put($backup, json_encode([
                'configs' => $configs, 'article_id' => $article?->id, 'article_content' => $translations,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            $count = DB::table('treasure_map_configs')->where('reputation_reward', '!=', 10)
                ->update(['reputation_reward' => 10, 'updated_at' => now()]);
            if ($article !== null && $translations !== $updated) {
                $article->setTranslations('content', $updated);
                if (Schema::hasColumn('library_articles', 'content_legacy')) {
                    $article->setAttribute('content_legacy', $article->getTranslation('content', 'ru'));
                }
                $article->save();
            }
            $this->command?->info('Награда изменена на 10 репутации у карт: '.$count.'. Копия прежних данных: '.$backup);
        });
    }
}
