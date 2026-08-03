<?php

namespace App\Modules\Shared\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    /**
     * Store file with unique name
     */
    public static function store(
        UploadedFile $file,
        string $directory,
        string $disk = 'public'
    ): ?string {
        // Generate unique filename to prevent overwriting
        $name = $file->getClientOriginalName();
        // $uniqueName = time() . '_' . uniqid() . '.' . $extension;

        $path = $file->storeAs($directory, $name, $disk);

        return $path;
    }
    /**
     * Delete file
     */
    public static function delete(
        ?string $path,
        string $disk = 'public'
    ): void {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }
}
