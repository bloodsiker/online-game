<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class UpdateTreasureMapSearchBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $values = ['worn' => [50, 35], 'ancient' => [40, 52], 'mysterious' => [30, 77], 'forgotten' => [20, 103], 'legendary' => [10, 136]];
        DB::transaction(function () use ($values): void {
            $configs = TreasureMapConfig::with('item')->whereIn('code', array_keys($values))->orderBy('id')->lockForUpdate()->get();
            if ($configs->count() !== count($values)) {
                throw new \RuntimeException('Не найдены все пять стартовых карт сокровищ.');
            }
            $article = LibraryArticle::where('slug', 'kladoiskatel')->lockForUpdate()->first();
            $before = $article?->getTranslations('content') ?? [];
            $after = $before;
            foreach ($before as $locale => $content) {
                foreach ($configs as $config) {
                    [$chance, $seconds] = $values[$config->code];
                    $name = preg_quote(e($config->item->getTranslation('name', $locale)), '~');
                    $content = preg_replace_callback(
                        '~(<tr>\s*<td>'.$name.'</td>\s*<td\b[^>]*>\d+</td>\s*<td\b[^>]*>)[\d.]+%(\s*</td>\s*<td\b[^>]*>)\d+(\s*(?:сек\.|с)\s*</td>)~u',
                        static fn ($match) => $match[1].$chance.'%'.$match[2].$seconds.$match[3],
                        $content, -1, $matches,
                    );
                    if ($matches !== 1) {
                        throw new \RuntimeException('Не найдена строка параметров карты #'.$config->share_item_id.' в статье: '.$locale);
                    }
                }
                $after[$locale] = str_replace('70% + 5 п.п. = 75%', '50% + 5 п.п. = 55%', $content);
            }
            $changed = $configs->contains(fn ($config) => [$config->success_chance_percent, (int) $config->search_duration_seconds] !== [(float) $values[$config->code][0], $values[$config->code][1]]);
            if (! $changed && $before === $after) {
                $this->command?->info('Параметры карт и статья уже обновлены.');

                return;
            }
            $backup = 'backups/treasure-hunt/search-balance-before-'.now()->format('Ymd-His-u').'.json';
            Storage::disk('local')->put($backup, json_encode([
                'configs' => $configs->map(fn ($config) => $config->getRawOriginal())->all(),
                'article_id' => $article?->id, 'article_content' => $before,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            foreach ($configs as $config) {
                [$chance, $seconds] = $values[$config->code];
                $config->fill(['success_chance_percent' => $chance, 'search_duration_seconds' => $seconds])->save();
            }
            if ($article !== null && $before !== $after) {
                $article->setTranslations('content', $after);
                if (Schema::hasColumn('library_articles', 'content_legacy')) {
                    $article->setAttribute('content_legacy', $article->getTranslation('content', 'ru'));
                }
                $article->save();
            }
            $this->command?->info('Шансы и время поиска обновлены для пяти карт. Резервная копия: '.$backup);
        });
    }
}
