<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileService
{
    public function deleteFile($fileModel)
    {
        $prev_url = $fileModel->url;

        $prev_path = parse_url($prev_url, PHP_URL_PATH); // Extracts the path part of the URL

        // Remove the '/storage' prefix from the path
        $pathWithoutStorage = str_replace('/storage', '', $prev_path);

        // Normalize and validate the path to prevent directory traversal
        $normalizedPath = str_replace(['../', '..\\'], '', $pathWithoutStorage);
        $normalizedPath = ltrim($normalizedPath, '/');

        // Ensure path is within the public disk
        $fullPath = 'public/' . $normalizedPath;

        // Additional validation: check if file exists and is within storage
        if (!Storage::disk('public')->exists($normalizedPath)) {
            throw new \Exception('File not found');
        }

        // Get absolute path and verify it's within storage directory
        $absolutePath = Storage::disk('public')->path($normalizedPath);
        $storagePath = Storage::disk('public')->path('');

        if (!str_starts_with($absolutePath, $storagePath)) {
            throw new \Exception('Invalid file path');
        }

        Storage::delete($fullPath);

        return $fileModel;
    }

    public function storeFile($fileModel)
    {
        $file_name = uniqid() . '_' . $fileModel->hashName();

        $path = $fileModel->storeAs('media', $file_name, 'public');

        $url = url(Storage::url($path));

        return $url;
    }

    public function getMime($media): string
    {
        if (str()->contains($media->getMimeType(), 'video')) {
            return 'video';
        } else {
            return 'image';
        }
    }
}
