<?php

namespace App\Contracts\Communications;

use Illuminate\Http\UploadedFile;

interface AssetStorageInterface
{
    /**
     * Store an uploaded file and return its generated path.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    public function store(UploadedFile $file, string $folder = 'templates'): string;

    /**
     * Delete an asset from storage.
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool;

    /**
     * Get the publicly accessible URL for the given path.
     *
     * @param string $path
     * @return string
     */
    public function url(string $path): string;
}
