<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'surname' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['sometimes', 'required', 'string', 'max:20'],
            'department' => ['sometimes', 'required', 'string', 'max:255'],
            'position' => ['sometimes', 'required', 'string', 'max:255'],
            'profile_photo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'bio' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('profile_photo_path')) {
            \Log::info('Profile photo upload detected (update)', [
                'file_name' => $request->file('profile_photo_path')->getClientOriginalName(),
                'file_size' => $request->file('profile_photo_path')->getSize(),
            ]);

            // Delete old photo from Cloudinary if it exists
            if ($user->profile_photo_path && CloudinaryService::isCloudinaryUrl($user->profile_photo_path)) {
                CloudinaryService::deleteImage($user->profile_photo_path);
            }

            $url = CloudinaryService::uploadImage($request->file('profile_photo_path'), 'profile-photos');
            if ($url) {
                $validatedData['profile_photo_path'] = $url;
                \Log::info('Profile photo URL saved to validatedData (update)', ['url' => $url]);
            } else {
                \Log::error('Cloudinary upload returned null (update)');
                return response()->json([
                    'message' => 'Failed to upload profile photo to Cloudinary. Please check server logs.'
                ], 500);
            }
        }

        \Log::info('Updating user (update)', ['validated_data_keys' => array_keys($validatedData)]);
        $user->update($validatedData);
        \Log::info('User updated (update)', ['profile_photo_path' => $user->fresh()->profile_photo_path]);

        return response()->json($user->fresh());
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['required', 'string', 'max:20'],
            'department' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:user,admin'],
            'profile_photo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'bio' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('profile_photo_path')) {
            \Log::info('Profile photo upload detected (updateUser)', [
                'file_name' => $request->file('profile_photo_path')->getClientOriginalName(),
                'file_size' => $request->file('profile_photo_path')->getSize(),
            ]);

            // Delete old photo from Cloudinary if it exists
            if ($user->profile_photo_path && CloudinaryService::isCloudinaryUrl($user->profile_photo_path)) {
                CloudinaryService::deleteImage($user->profile_photo_path);
            }

            $url = CloudinaryService::uploadImage($request->file('profile_photo_path'), 'profile-photos');
            if ($url) {
                $validatedData['profile_photo_path'] = $url;
                \Log::info('Profile photo URL saved to validatedData (updateUser)', ['url' => $url]);
            } else {
                \Log::error('Cloudinary upload returned null (updateUser)');
                return response()->json([
                    'message' => 'Failed to upload profile photo to Cloudinary. Please check server logs.'
                ], 500);
            }
        }

        \Log::info('Updating user', ['validated_data_keys' => array_keys($validatedData)]);
        $user->update($validatedData);
        \Log::info('User updated', ['profile_photo_path' => $user->fresh()->profile_photo_path]);

        return response()->json($user->fresh());
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }

        // Delete profile photo from Cloudinary if exists
        if ($user->profile_photo_path && CloudinaryService::isCloudinaryUrl($user->profile_photo_path)) {
            CloudinaryService::deleteImage($user->profile_photo_path);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
