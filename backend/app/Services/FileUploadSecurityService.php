<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class FileUploadSecurityService
{
    // Allowed MIME types
    private static $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp',
    ];

    // Allowed file extensions
    private static $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'webp'
    ];

    // Maximum file size in bytes (5MB)
    private static $maxFileSize = 5242880;

    /**
     * Validate and sanitize file upload
     */
    public static function validateFile(UploadedFile $file): array
    {
        // Check if file exists
        if (!$file->isValid()) {
            throw new Exception("Uploaded file is invalid: " . $file->getErrorMessage());
        }

        // Check file size
        if ($file->getSize() > self::$maxFileSize) {
            throw new Exception("File size exceeds maximum allowed size of 5MB");
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::$allowedMimeTypes)) {
            throw new Exception("File type {$mimeType} is not allowed. Allowed types: JPEG, PNG, WebP");
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::$allowedExtensions)) {
            throw new Exception("File extension .{$extension} is not allowed");
        }

        // Additional security: verify actual file content matches extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualMimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        if (!in_array($actualMimeType, self::$allowedMimeTypes)) {
            throw new Exception("File content does not match claimed type");
        }

        return [
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $file->getSize(),
            'actual_mime_type' => $actualMimeType,
        ];
    }

    /**
     * Generate a secure random filename
     */
    public static function generateSecureFilename(string $originalExtension): string
    {
        // Format: {random32chars}_{timestamp}.{ext}
        return Str::random(32) . '_' . now()->getTimestamp() . '.' . strtolower($originalExtension);
    }

    /**
     * Upload file securely
     */
    public static function uploadFile(
        UploadedFile $file,
        string $directory = 'uploads',
        bool $isPublic = false
    ): array {
        try {
            // Validate file
            $fileInfo = self::validateFile($file);

            // Generate secure filename
            $filename = self::generateSecureFilename($fileInfo['extension']);

            // Determine disk (public or private)
            $disk = $isPublic ? 'public' : 'private';

            // Store file
            $path = Storage::disk($disk)->putFileAs(
                $directory,
                $file,
                $filename
            );

            if (!$path) {
                throw new Exception("Failed to store file");
            }

            // Generate URL based on disk
            $url = $isPublic 
                ? asset('storage/' . $path)
                : null; // Private files should not have URLs

            Log::info("File uploaded successfully", [
                'filename' => $filename,
                'path' => $path,
                'directory' => $directory,
                'disk' => $disk,
                'size' => $fileInfo['size'],
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'disk' => $disk,
                'size' => $fileInfo['size'],
                'mime_type' => $fileInfo['mime_type'],
            ];

        } catch (Exception $e) {
            Log::error("File upload failed: {$e->getMessage()}", [
                'original_name' => $file->getClientOriginalName(),
                'directory' => $directory,
            ]);
            throw $e;
        }
    }

    /**
     * Upload image file with additional image validation
     */
    public static function uploadImage(
        UploadedFile $file,
        string $directory = 'images'
    ): array {
        try {
            // Validate file
            $fileInfo = self::validateFile($file);

            // Verify it's actually an image
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                throw new Exception("File is not a valid image");
            }

            // Check image dimensions (minimum 100x100, maximum 4000x4000)
            $width = $imageInfo[0];
            $height = $imageInfo[1];

            if ($width < 100 || $height < 100) {
                throw new Exception("Image dimensions must be at least 100x100 pixels");
            }

            if ($width > 4000 || $height > 4000) {
                throw new Exception("Image dimensions must not exceed 4000x4000 pixels");
            }

            // Generate secure filename
            $filename = self::generateSecureFilename($fileInfo['extension']);

            // Store file in private directory
            $path = Storage::disk('private')->putFileAs(
                $directory,
                $file,
                $filename
            );

            if (!$path) {
                throw new Exception("Failed to store image");
            }

            Log::info("Image uploaded successfully", [
                'filename' => $filename,
                'path' => $path,
                'directory' => $directory,
                'width' => $width,
                'height' => $height,
                'size' => $fileInfo['size'],
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $path,
                'directory' => $directory,
                'width' => $width,
                'height' => $height,
                'size' => $fileInfo['size'],
            ];

        } catch (Exception $e) {
            Log::error("Image upload failed: {$e->getMessage()}", [
                'original_name' => $file->getClientOriginalName(),
                'directory' => $directory,
            ]);
            throw $e;
        }
    }

    /**
     * Delete file securely
     */
    public static function deleteFile(string $path, string $disk = 'private'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                Log::info("File deleted successfully", [
                    'path' => $path,
                    'disk' => $disk,
                ]);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error("File deletion failed: {$e->getMessage()}", [
                'path' => $path,
                'disk' => $disk,
            ]);
            throw $e;
        }
    }

    /**
     * Get file from secure location
     */
    public static function getFile(string $path, string $disk = 'private'): string
    {
        try {
            if (!Storage::disk($disk)->exists($path)) {
                throw new Exception("File not found");
            }
            return Storage::disk($disk)->get($path);
        } catch (Exception $e) {
            Log::error("File retrieval failed: {$e->getMessage()}", [
                'path' => $path,
                'disk' => $disk,
            ]);
            throw $e;
        }
    }
}
