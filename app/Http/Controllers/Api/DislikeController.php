<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class DislikeController extends Controller
{
    public function store(Request $request, Blog $blog)
    {
        $attributes = [];
        if (auth()->check()) {
            $attributes['user_id'] = auth()->id();
        } else {
            $attributes['ip_address'] = $request->ip();
        }

        // Remove like if it exists
        $blog->likes()->where($attributes)->delete();

        // Create the dislike
        $dislike = $blog->dislikes()->firstOrCreate($attributes);

        return response()->json($dislike, 201);
    }

    public function destroy(Request $request, Blog $blog)
    {
        $attributes = [];
        if (auth()->check()) {
            $attributes['user_id'] = auth()->id();
        } else {
            $attributes['ip_address'] = $request->ip();
        }

        $blog->dislikes()->where($attributes)->delete();

        return response()->json(null, 204);
    }
}
