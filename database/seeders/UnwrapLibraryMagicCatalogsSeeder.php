<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UnwrapLibraryMagicCatalogsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (['passivnye-magicheskie-navyki' => 'passive', 'baffy-debaffy-i-istselenie' => 'support'] as $slug => $group) {
                $article = LibraryArticle::where('slug', $slug)->lockForUpdate()->first();
                if ($article === null) {
                    continue;
                }
                $original = $article->getTranslations('content');
                $updated = $original;
                foreach ($original as $locale => $html) {
                    $marker = '[[magic_skill_catalog:'.$group.']]';
                    $markerAt = strpos($html, $marker);
                    if ($markerAt === false) {
                        continue;
                    }
                    $start = strrpos(substr($html, 0, $markerAt), '<table class="library-game-frame"');
                    $end = strpos($html, '</table>', $markerAt);
                    if ($start === false || $end === false) {
                        continue;
                    }
                    $frame = substr($html, $start, $end + strlen('</table>') - $start);
                    if (! preg_match('/<td class="tbl-usi_bg"[^>]*>(.*?)<\/td>/s', $frame, $matches)
                        || ! str_contains($matches[1], $marker)) {
                        continue;
                    }
                    $updated[$locale] = substr_replace($html, $matches[1], $start, strlen($frame));
                }
                if ($updated === $original) {
                    continue;
                }
                $path = 'backups/library/magic-catalog-layout-'.$article->id.'-'.now()->format('Ymd-His-u').'.json';
                Storage::disk('local')->put($path, json_encode($original, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
                $article->setTranslations('content', $updated);
                $article->save();
                $this->command?->info('Убрана внешняя рамка каталога: '.$slug);
            }
        });
    }
}
