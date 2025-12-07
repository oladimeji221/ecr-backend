<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageUploadController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'image.required' => 'Please select an image to upload.',
                'image.image' => 'The uploaded file must be an image.',
                'image.mimes' => 'The image must be a JPEG, PNG, GIF, or SVG file.',
                'image.max' => 'The image size must not exceed 2MB. Please compress or resize your image.',
            ]);

            $url = CloudinaryService::uploadImage($request->file('image'), 'content_images');

            if (!$url) {
                return response()->json([
                    'message' => 'Failed to upload image to Cloudinary. Please try again later.'
                ], 500);
            }

            return response()->json([
                'url' => $url
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Image validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Image upload error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload image. Please try again later.'
            ], 500);
        }
    }
}