<?php

namespace App\Services\Communications\Storage;

use App\Contracts\Communications\AssetStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LocalStorageProvider implements AssetStorageInterface
{
    protected string $disk = 'public';

    public function store(UploadedFile $file, string $folder = 'templates'): string
    {
        $path = "campaign-assets/{$folder}";
        return $file->store($path, $this->disk);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function url(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }
}
