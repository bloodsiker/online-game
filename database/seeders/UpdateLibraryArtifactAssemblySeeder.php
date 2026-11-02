<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class UpdateLibraryArtifactAssemblySeeder extends Seeder
{
    public function run(): void
    {
        $assemblies = ItemAssembly::query()->with(['components', 'rewards'])
            ->where('type', 'artifact')->where('is_active', true)->whereIn('id', [2, 3, 4])->orderBy('id')->get();
        if ($assemblies->isEmpty()) {
            throw new RuntimeException('Активные сборки не найдены.');
        }
        foreach ($assemblies as $assembly) {
            if ($assembly->components->isEmpty() || $assembly->rewards->isEmpty()) {
                throw new RuntimeException('Сборка не содержит компонентов или наград.');
            }
        }

        DB::transaction(function () use ($assemblies): void {
            $article = LibraryArticle::query()->where('slug', 'sborka-artefaktov')->lockForUpdate()->firstOrFail();
            $backup = 'backups/library/sborka-artefaktov-'.$article->id.'-'.now()->format('Ymd_His_u').'.json';
            if (! Storage::disk('local')->put($backup, json_encode($article->getAttributes(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR))) {
                throw new RuntimeException('Не удалось сохранить резервную копию статьи.');
            }

            foreach (['ru', 'uk'] as $locale) {
                $content = (string) $article->getTranslation('content', $locale, false);
                foreach ($assemblies as $assembly) {
                    $section = $this->section($assembly, $locale);
                    $start = '<!-- artifact-assembly:'.$assembly->id.':start -->';
                    $end = '<!-- artifact-assembly:'.$assembly->id.':end -->';
                    if (str_contains($content, $start)) {
                        $content = preg_replace('/'.preg_quote($start, '/').'.*?'.preg_quote($end, '/').'/s', $section, $content, 1);
                    } else {
                        $anchor = '<div class="library-info-block library-info-block--important">';
                        if (! str_contains($content, $anchor)) {
                            throw new RuntimeException('Не найден блок для безопасного обновления статьи: '.$locale);
                        }
                        $content = str_replace($anchor, $section."\n".$anchor, $content);
                    }
                }
                $content = str_replace(
                    $locale === 'uk' ? '<h2>Перший набір уламків</h2>' : '<h2>Первый набор осколков</h2>',
                    $locale === 'uk' ? '<h2>Звичайні артефакти: чотири уламки</h2>' : '<h2>Обычные артефакты: четыре осколка</h2>',
                    $content,
                );
                $article->setTranslation('content', $locale, $content);
                $article->setTranslation('excerpt', $locale, $assemblies->contains('id', 4)
                    ? ($locale === 'uk'
                        ? 'Звичайні, незвичайні, рідкісні та епічні артефакти: набори з чотирьох–семи уламків, компоненти та результати складання в кузні.'
                        : 'Обычные, необычные, редкие и эпические артефакты: наборы из четырёх–семи осколков, компоненты и результаты сборки в кузне.')
                    : ($assemblies->contains('id', 3)
                    ? ($locale === 'uk'
                        ? 'Звичайні, незвичайні та рідкісні артефакти: набори з чотирьох, п’яти й шести уламків, компоненти та результати складання в кузні.'
                        : 'Обычные, необычные и редкие артефакты: наборы из четырёх, пяти и шести осколков, компоненты и результаты сборки в кузне.')
                    : ($locale === 'uk'
                        ? 'Звичайні та незвичайні артефакти: набори з чотирьох і п’яти уламків, компоненти та можливі результати складання в кузні.'
                        : 'Обычные и необычные артефакты: наборы из четырёх и пяти осколков, компоненты и возможные результаты сборки в кузне.')));
            }
            $article->save();
            $this->command?->info('Статья обновлена. Резервная копия: storage/app/'.$backup);
        });
    }

    private function section(ItemAssembly $assembly, string $locale): string
    {
        $components = $assembly->components->map(fn ($item): string => e($item->getTranslation('name', $locale)).' ×'.(int) $item->pivot->count)->implode(', ');
        [$heading, $intro, $rewardsHeading] = $locale === 'uk'
            ? match ($assembly->id) {
                2 => ['Незвичайні артефакти: п’ять уламків', 'Для цієї збірки потрібні п’ять різних уламків — по одному кожного. Це окремий набір: звичайні уламки з попередньої збірки їх не замінюють.', 'Можливі незвичайні артефакти'],
                3 => ['Рідкісні артефакти: шість уламків', 'Для цієї збірки потрібні шість різних рідкісних уламків — по одному кожного. Звичайні та незвичайні уламки для неї не підходять.', 'Можливі рідкісні артефакти'],
                4 => ['Епічні артефакти: сім уламків', 'Для цієї збірки потрібні сім різних епічних уламків — по одному кожного. Уламки інших рідкостей їх не замінюють. Повний набір витрачається на отримання одного випадкового епічного артефакту.', 'Можливі епічні артефакти'],
            }
        : match ($assembly->id) {
            2 => ['Необычные артефакты: пять осколков', 'Для этой сборки нужны пять разных осколков — по одному каждого. Это отдельный набор: обычные осколки из предыдущей сборки их не заменяют.', 'Возможные необычные артефакты'],
            3 => ['Редкие артефакты: шесть осколков', 'Для этой сборки нужны шесть разных редких осколков — по одному каждого. Обычные и необычные осколки для неё не подходят.', 'Возможные редкие артефакты'],
            4 => ['Эпические артефакты: семь осколков', 'Для этой сборки нужны семь разных эпических осколков — по одному каждого. Осколки других редкостей их не заменяют. Полный набор расходуется на получение одного случайного эпического артефакта.', 'Возможные эпические артефакты'],
        };
        $result = $locale === 'uk'
            ? 'За один повний набір ви отримуєте один випадковий артефакт із наведених нижче. Усі компоненти набору витрачаються на одну збірку.'
            : 'За один полный набор вы получаете один случайный артефакт из приведённых ниже. Все компоненты набора расходуются на одну сборку.';

        return '<!-- artifact-assembly:'.$assembly->id.':start -->'."\n"
            .'<h2>'.e($heading).'</h2><p>'.e($intro).'</p>'
            .'<p>'.$components.'.</p>'
            .$this->frame($assembly->components)
            .'<h3>'.e($rewardsHeading).'</h3><p>'.e($result).'</p>'
            .$this->frame($assembly->rewards)
            .'<p>'.$assembly->rewards->map(fn ($item): string => e($item->getTranslation('name', $locale)))->implode(', ').'.</p>'
            ."\n".'<!-- artifact-assembly:'.$assembly->id.':end -->';
    }

    private function frame(Collection $items): string
    {
        $shortcodes = $items->map(fn ($item): string => '[[item:'.$item->id.((int) $item->pivot->count > 1 ? '; count:'.(int) $item->pivot->count : '').']]')->implode(' ');

        return '<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody>'
            .'<tr height="22"><td width="20" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt"></td><td width="20" class="tbl-shp-sml rt"><b></b></td></tr>'
            .'<tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" align="center" style="padding:8px 10px"><div class="structures">'.$shortcodes.'</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr>'
            .'<tr height="18"><td width="20" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb">&nbsp;</td><td width="20" class="tbl-shp-sml rb"><b></b></td></tr>'
            .'</tbody></table>';
    }
}
