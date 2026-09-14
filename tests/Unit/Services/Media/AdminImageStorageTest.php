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

    public function test_it_creates_monthly_directories_inside_public_path(): void
    {
        Carbon::setTestNow('2026-02-01 00:00:00');
        $publicPath = storage_path('framework/testing/admin-image-storage');
        File::deleteDirectory($publicPath);
        $this->app->usePublicPath($publicPath);

        try {
            $path = app(AdminImageStorage::class)->storeInPublicDirectory(
                UploadedFile::fake()->image('cover.jpg'),
                'library/covers',
            );

            $this->assertMatchesRegularExpression('#^library/covers/2026/02/[a-f0-9-]+\.jpg$#', $path);
            $this->assertFileExists($publicPath.'/'.$path);
        } finally {
            File::deleteDirectory($publicPath);
            Carbon::setTestNow();
        }
    }
}
