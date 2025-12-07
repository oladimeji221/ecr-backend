<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, Blog $blog)
    {
        $attributes = [];
        if (auth()->check()) {
            $attributes['user_id'] = auth()->id();
        } else {
            $attributes['ip_address'] = $request->ip();
        }

        // Remove dislike if it exists
        $blog->dislikes()->where($attributes)->delete();

        // Create the like
        $like = $blog->likes()->firstOrCreate($attributes);

        return response()->json($like, 201);
    }

    public function destroy(Request $request, Blog $blog)
    {
        $attributes = [];
        if (auth()->check()) {
            $attributes['user_id'] = auth()->id();
        } else {
            $attributes['ip_address'] = $request->ip();
        }

        $blog->likes()->where($attributes)->delete();

        return response()->json(null, 204);
    }
}