<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

final class AdminImageStorage
{
    public function storeOnPublicDisk(UploadedFile $file, string $directory): string
    {
        $path = $file->store($this->monthlyDirectory($directory), 'public');

        if (! is_string($path)) {
            throw new RuntimeException('Не удалось сохранить изображение.');
        }

        return $path;
    }

    public function storeInPublicDirectory(UploadedFile $file, string $directory): string
    {
        $monthlyDirectory = $this->monthlyDirectory($directory);
        File::ensureDirectoryExists(public_path($monthlyDirectory));

        $filename = Str::uuid()->toString().'.'.$file->extension();
        $file->move(public_path($monthlyDirectory), $filename);

        return $monthlyDirectory.'/'.$filename;
    }

    private function monthlyDirectory(string $directory): string
    {
        $directory = trim($directory, '/');

        if ($directory === '') {
            throw new RuntimeException('Не указана директория для изображения.');
        }

        return $directory.'/'.now()->format('Y/m');
    }
}
