<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Blog $blog)
    {
        $user = auth('sanctum')->user() ?? auth()->user();

        Log::info('Incoming comment request', [
            'blog_id' => $blog->id,
            'user_id' => $user?->id,
            'ip' => $request->ip(),
            'payload' => $request->only(['name', 'email', 'content']),
        ]);

        $validated = $request->validate([
            'content' => 'required|string',
            'name' => [Rule::requiredIf(!$user), 'string', 'max:255'],
            'email' => [Rule::requiredIf(!$user), 'email', 'max:255'],
        ]);

        $commentData = [
            'content' => $validated['content'],
        ];

        if ($user) {
            $commentData['user_id'] = $user->id;
            $commentData['name'] = $user->name;
            $commentData['email'] = $user->email;
        } else {
            $commentData['ip_address'] = $request->ip();
            $commentData['name'] = $validated['name'];
            $commentData['email'] = $validated['email'];
        }

        try {
            $comment = $blog->comments()->create($commentData);
            return response()->json($comment->load('user'), 201);
        } catch (\Exception $e) {
            Log::error('Comment creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Your comment could not be posted.'], 500);
        }
    }
}