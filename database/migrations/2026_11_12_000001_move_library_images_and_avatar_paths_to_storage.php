<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * 1. Картинки библиотеки переезжают из public/library (жили в git и в папке релиза — загрузка через
 *    админку на проде терялась при следующем деплое) на диск 'public' (storage/app/public → shared/storage).
 *    Относительный путь в БД ('library/categories/2026/09/x.webp') остаётся тем же — теперь это путь диска;
 *    public_media_url() отдаёт его через /storage.
 * 2. В HTML статей адреса /library/content/... (с доменом или без) → /storage/library/content/...
 * 3. Аватары профиля: вместо полного адреса с доменом ('http://host/storage/race-avatars/x.jpg')
 *    или пути с префиксом ссылки ('storage/race-avatars/x.jpg') храним путь диска/статики
 *    ('race-avatars/x.jpg', 'main/images/...').
 *
 * Идемпотентна: уже скопированные файлы и уже исправленные пути не трогает.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->copyLibraryFiles();
        $this->rewriteArticleContent();
        $this->relativizeAvatars();
    }

    public function down(): void
    {
        // Файлы в storage и относительные пути остаются: старый код читал те же относительные пути.
    }

    private function copyLibraryFiles(): void
    {
        $source = public_path('library');
        if (! File::isDirectory($source)) {
            return;
        }
        $disk = Storage::disk('public');
        foreach (File::allFiles($source) as $file) {
            $path = 'library/'.str_replace('\\', '/', $file->getRelativePathname());
            if (! $disk->exists($path)) {
                $disk->put($path, File::get($file->getPathname()));
            }
        }
    }

    private function rewriteArticleContent(): void
    {
        if (! Schema::hasTable('library_articles') || ! Schema::hasColumn('library_articles', 'content')) {
            return;
        }
        DB::table('library_articles')->where('content', 'like', '%library%content%')->orderBy('id')
            ->each(function (object $article): void {
                $content = (string) $article->content;
                $decoded = json_decode($content, true);
                $updated = is_array($decoded)
                    ? json_encode(array_map(fn ($html) => is_string($html) ? $this->rewriteHtml($html) : $html, $decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : $this->rewriteHtml($content);
                if ($updated !== false && $updated !== $content) {
                    DB::table('library_articles')->where('id', $article->id)->update(['content' => $updated]);
                }
            });
    }

    private function rewriteHtml(string $html): string
    {
        // (src|href)="[http(s)://host]/library/content/..." → "/storage/library/content/..."
        return (string) preg_replace('~((?:src|href)\s*=\s*["\'])(?:https?://[^/"\']+)?/library/content/~i', '$1/storage/library/content/', $html);
    }

    private function relativizeAvatars(): void
    {
        if (! Schema::hasTable('user_profiles') || ! Schema::hasColumn('user_profiles', 'avatar')) {
            return;
        }
        DB::table('user_profiles')->where(fn ($query) => $query->where('avatar', 'like', 'http://%')->orWhere('avatar', 'like', 'https://%')
            ->orWhere('avatar', 'like', 'storage/%')->orWhere('avatar', 'like', '/storage/%'))
            ->orderBy('id')->select(['id', 'avatar'])
            ->each(function (object $profile): void {
                $path = preg_replace('~^/?storage/~', '', public_media_path((string) $profile->avatar));
                if ($path !== $profile->avatar && $path !== '') {
                    DB::table('user_profiles')->where('id', $profile->id)->update(['avatar' => $path]);
                }
            });
    }
};
