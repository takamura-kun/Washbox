<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureFileUploadService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/jpg',
    ];

    private const MAX_FILE_SIZE = 5120; // 5MB in KB

    /**
     * Securely upload and validate image file
     */
    public static function uploadImage(UploadedFile $file, string $directory): array
    {
        // 1. Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE * 1024) {
            throw new \Exception('File size exceeds 5MB limit');
        }

        // 2. Validate MIME type (actual content, not just extension)
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \Exception('Invalid file type. Only JPEG and PNG images are allowed');
        }

        // 3. Validate file is actually an image (read image data)
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            throw new \Exception('File is not a valid image');
        }

        // 4. Check image dimensions (prevent extremely large images)
        [$width, $height] = $imageInfo;
        if ($width > 4096 || $height > 4096) {
            throw new \Exception('Image dimensions too large. Maximum 4096x4096 pixels');
        }

        // 5. Generate secure random filename (prevent path traversal)
        $extension = $file->getClientOriginalExtension();
        $filename = now()->timestamp . '_' . Str::random(32) . '.' . $extension;

        // 6. Store file in secure location (storage/app/public)
        $path = $file->storeAs($directory, $filename, 'public');

        if (!$path) {
            throw new \Exception('Failed to store file');
        }

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => Storage::url($path),
            'size' => $file->getSize(),
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Delete uploaded file
     */
    public static function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    /**
     * Validate file before upload
     */
    public static function validateImage(UploadedFile $file): bool
    {
        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            return false;
        }

        // Check if it's actually an image
        if (@getimagesize($file->getRealPath()) === false) {
            return false;
        }

        return true;
    }
}
