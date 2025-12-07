<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    /**
     * Get Cloudinary instance
     *
     * @return Cloudinary
     */
    private static function getCloudinary(): Cloudinary
    {
        $cloudinaryUrl = config('filesystems.disks.cloudinary.url') ?? config('cloudinary.cloud_url');
        return new Cloudinary($cloudinaryUrl);
    }

    /**
     * Upload an image to Cloudinary and return the secure URL
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string|null
     */
    public static function uploadImage(UploadedFile $file, string $folder = 'general'): ?string
    {
        try {
            Log::info('Cloudinary upload started', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'folder' => $folder,
            ]);

            $cloudinary = self::getCloudinary();
            $uploadResult = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'image',
            ]);

            // Try to access as object properties first (most reliable)
            if (is_object($uploadResult)) {
                $securePath = $uploadResult->secure_url ?? (property_exists($uploadResult, 'secure_url') ? $uploadResult->secure_url : null) ?? $uploadResult->url ?? null;
                $publicId = $uploadResult->public_id ?? null;
                
                if ($securePath) {
                    Log::info('Cloudinary upload successful (object property access)', [
                        'url' => $securePath,
                        'public_id' => $publicId,
                    ]);
                    return $securePath;
                }
            }
            
            // Fallback to array access
            $resultArray = is_array($uploadResult) ? $uploadResult : (array)$uploadResult;
            $securePath = $resultArray['secure_url'] ?? $resultArray['url'] ?? null;
            $publicId = $resultArray['public_id'] ?? null;

            if (!$securePath) {
                Log::error('Cloudinary upload result does not contain secure_url', [
                    'result_type' => gettype($uploadResult),
                    'result' => $resultArray,
                    'available_keys' => array_keys($resultArray),
                ]);
                return null;
            }

            Log::info('Cloudinary upload successful', [
                'url' => $securePath,
                'public_id' => $publicId,
            ]);

            return $securePath;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Delete an image from Cloudinary using its URL
     *
     * @param string $url
     * @return bool
     */
    public static function deleteImage(string $url): bool
    {
        try {
            // Extract public_id from URL
            // Cloudinary URL format: https://res.cloudinary.com/{cloud_name}/image/upload/{version}/{public_id}.{format}
            $publicId = self::extractPublicId($url);
            
            if (!$publicId) {
                return false;
            }

            $cloudinary = self::getCloudinary();
            $cloudinary->uploadApi()->destroy($publicId);
            return true;
        } catch (\Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract public_id from Cloudinary URL
     *
     * @param string $url
     * @return string|null
     */
    private static function extractPublicId(string $url): ?string
    {
        // Check if it's a Cloudinary URL
        if (!str_contains($url, 'cloudinary.com')) {
            return null;
        }

        // Extract public_id from URL
        // Format: https://res.cloudinary.com/{cloud_name}/image/upload/{version}/{folder}/{public_id}.{format}
        // or: https://res.cloudinary.com/{cloud_name}/image/upload/{folder}/{public_id}.{format}
        // or: https://res.cloudinary.com/{cloud_name}/image/upload/{version}/{public_id}.{format}
        // The public_id includes the folder path
        $pattern = '/\/image\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/';
        
        if (preg_match($pattern, $url, $matches)) {
            // Remove file extension if present
            $publicId = $matches[1];
            // Remove trailing extension
            $publicId = preg_replace('/\.[^.]+$/', '', $publicId);
            return $publicId;
        }

        return null;
    }

    /**
     * Check if URL is a Cloudinary URL
     *
     * @param string $url
     * @return bool
     */
    public static function isCloudinaryUrl(string $url): bool
    {
        return str_contains($url, 'cloudinary.com');
    }
}

