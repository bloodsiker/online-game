<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Media;

use App\Services\Media\AdminImageStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AdminImageStorageTest extends TestCase
{
    public function test_it_stores_images_in_year_and_month_subdirectories(): void
    {
        Carbon::setTestNow('2026-01-15 12:00:00');
        Storage::fake('public');

        $path = app(AdminImageStorage::class)->storeOnPublicDisk(
            UploadedFile::fake()->image('item.webp'),
            'items',
        );

        $this->assertMatchesRegularExpression('#^items/2026/01/[a-zA-Z0-9_-]+\.webp$#', $path);
        Storage::disk('public')->assertExists($path);

        Carbon::setTestNow();
    }

    public function test_media_url_prefers_public_static_files_and_falls_back_to_storage(): void
    {
        Storage::fake('public');
        $publicPath = storage_path('framework/testing/media-url');
        File::ensureDirectoryExists($publicPath.'/main/images');
        File::put($publicPath.'/main/images/a.jpg', 'x');
        $this->app->usePublicPath($publicPath);

        try {
            $this->assertSame(asset('main/images/a.jpg'), public_media_url('main/images/a.jpg'));
            $this->assertSame(Storage::disk('public')->url('library/categories/2026/11/b.webp'), public_media_url('library/categories/2026/11/b.webp'));
            $this->assertSame('/storage/x.webp', public_media_url('/storage/x.webp'));
            $this->assertSame('https://cdn.example/x.webp', public_media_url('https://cdn.example/x.webp'));
            $this->assertSame(asset('img/avatar/dark_elf.jpg'), public_media_url(null, fallback: 'img/avatar/dark_elf.jpg'));
            $this->assertNull(public_media_url(''));

            $this->assertSame('race-avatars/2026/10/a.jpg', public_media_path('http://localhost:85/storage/race-avatars/2026/10/a.jpg'));
            $this->assertSame('main/images/data/user_pic/b.jpg', public_media_path('https://eternum-rpg.com/main/images/data/user_pic/b.jpg'));
            $this->assertSame('race-avatars/c.jpg', public_media_path('race-avatars/c.jpg'));
        } finally {
            File::deleteDirectory($publicPath);
        }
    }
}
