<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class GuestAuthController extends Controller
{
    /**
     * Handle a registration request for a new guest.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Common Fields
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:guests'],
            'phone_number' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'category' => ['required', 'in:learner,partner'],
            'agreed_to_terms' => ['required', 'boolean', 'accepted'],
            'device_name' => ['nullable', 'string'],

            // Partner-specific fields (required if category is partner)
            'expertise_category' => ['required_if:category,partner', 'nullable', 'string', 'max:255'],
            'role_specialization' => ['required_if:category,partner', 'nullable', 'string', 'max:255'],
            'contribution_type' => ['required_if:category,partner', 'nullable', 'array'],
            'short_bio' => ['required_if:category,partner', 'nullable', 'string'],
            'skills_or_tools' => ['required_if:category,partner', 'nullable', 'string'],
            'linkedin_link' => ['nullable', 'url', 'max:255'],
            'youtube_link' => ['nullable', 'url', 'max:255'],
            'medium_blog_link' => ['nullable', 'url', 'max:255'],
            'github_link' => ['nullable', 'url', 'max:255'],
            'sample_work_path' => ['nullable', 'file', 'mimes:pdf,ppt,pptx,mp4,mov,avi', 'max:20480'],

            // Learner-specific fields (required if category is learner)
            'primary_interest_area' => ['required_if:category,learner', 'nullable', 'string', 'max:255'],
            'learning_goal' => ['required_if:category,learner', 'nullable', 'string', 'max:255'],
            'guarantor_full_name' => ['nullable', 'string', 'max:255'],
            'guarantor_phone_number' => ['nullable', 'string', 'max:20'],
            'relationship_to_learner' => ['nullable', 'string', 'max:255'],
            'how_did_you_hear' => ['nullable', 'string', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $sampleWorkPath = null;
        if ($request->hasFile('sample_work_path')) {
            $sampleWorkPath = $request->file('sample_work_path')->store('guest_samples', 'public');
        }
        
        $guestData = array_merge($data, [
            'password' => Hash::make($data['password']),
            'sample_work_path' => $sampleWorkPath,
        ]);

        $guest = Guest::create($guestData);

        $token = $guest->createToken($request->device_name ?: 'api-guest', ['*'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $guest,
        ], 201);
    }

    /**
     * Handle a login request for a guest.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $guest = Guest::where('email', $request->email)->first();

        if (!$guest || !Hash::check($request->password, $guest->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $token = $guest->createToken($request->device_name ?: 'api-guest', ['*'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $guest,
        ], 200);
    }
}
