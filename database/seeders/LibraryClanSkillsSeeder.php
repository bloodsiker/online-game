<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LibraryClanSkillsSeeder extends Seeder
{
    private const ARTICLE_SLUG = 'klan-i-ego-vozmozhnosti';

    private const MARKER_START = '<!-- clan-skills-catalog:start -->';

    private const MARKER_END = '<!-- clan-skills-catalog:end -->';

    private const ANCHOR = '<p>Изучать навыки может участник с соответствующим полномочием. После улучшения бонус синхронизируется для всех членов клана.</p>';

    public function run(): void
    {
        DB::transaction(function (): void {
            $article = DB::table('library_articles')->where('slug', self::ARTICLE_SLUG)->first();
            if ($article === null) {
                throw new RuntimeException('Статья «Клан и его возможности» в библиотеке не найдена.');
            }

            $content = preg_replace(
                '/\s*'.preg_quote(self::MARKER_START, '/').'.*?'.preg_quote(self::MARKER_END, '/').'\s*/s',
                "\n",
                (string) $article->content,
            ) ?? (string) $article->content;

            if (! str_contains($content, self::ANCHOR)) {
                throw new RuntimeException('В статье не найден блок «Клановые навыки».');
            }

            $catalog = self::MARKER_START."\n[[clan_skill_catalog]]\n".self::MARKER_END;
            $content = str_replace(self::ANCHOR, self::ANCHOR."\n".$catalog, $content);

            DB::table('library_articles')->where('id', $article->id)->update([
                'content' => $content,
                'updated_at' => now(),
            ]);
        });

        $this->command?->info('В статью «Клан и его возможности» добавлена таблица клановых навыков.');
    }
}
