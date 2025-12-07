<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyUsersOfNewPost;
use App\Models\Blog;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Blog::with('category', 'user')->where('status', 'published');
        
        // Add search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%")
                  ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                      $categoryQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        return $query->get();
    }

    public function myBlogs()
    {
        return Blog::with('category', 'user')
            ->where('user_id', auth()->id())
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->normalizeOptionalFields($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:published,draft',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_robot' => 'nullable|string',
            'canonical_url' => 'nullable|string|url',
            'custom_url' => 'nullable|string|unique:blogs,custom_url',
            'json_ld' => 'nullable|json',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string|url',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = CloudinaryService::uploadImage($request->file('image'), 'blogs');
        }

        $ogImage = $this->resolveOgImage($request);

        $blog = Blog::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'meta_robot' => $validated['meta_robot'] ?? null,
            'category_id' => $validated['category_id'],
            'user_id' => auth()->id(),
            'status' => $validated['status'],
            'canonical_url' => $validated['canonical_url'] ?? null,
            'custom_url' => $validated['custom_url'] ?? null,
            'json_ld' => $validated['json_ld'] ?? null,
            'og_title' => $validated['og_title'] ?? null,
            'og_description' => $validated['og_description'] ?? null,
            'og_image' => $ogImage,
        ]);

        if ($blog->status === 'published') {
            NotifyUsersOfNewPost::dispatch($blog);
        }

        return response()->json($blog, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Blog $blog)
    {
        // Track view
        if (auth()->check()) {
            // User is authenticated, track by user_id
            $blog->views()->firstOrCreate([
                'user_id' => auth()->id(),
            ]);
        } else {
            // User is a guest, track by IP address
            $ip = $request->ip();
            $blog->views()->firstOrCreate([
                'ip_address' => $ip,
            ]);
        }

        return $blog->load('category', 'user', 'comments.user', 'likes', 'dislikes', 'views');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $this->normalizeOptionalFields($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:published,draft',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_robot' => 'nullable|string',
            'canonical_url' => 'nullable|string|url',
            'custom_url' => 'nullable|string|unique:blogs,custom_url,' . $blog->id,
            'json_ld' => 'nullable|json',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string|url',
        ]);

        $updateData = $validated;
        
        if ($request->hasFile('image')) {
            // Delete old image from Cloudinary if it exists
            if ($blog->image && CloudinaryService::isCloudinaryUrl($blog->image)) {
                CloudinaryService::deleteImage($blog->image);
            }
            $updateData['image'] = CloudinaryService::uploadImage($request->file('image'), 'blogs');
        }

        $updateData['json_ld'] = $validated['json_ld'] ?? null;
        $updateData['meta_title'] = $validated['meta_title'] ?? null;
        $updateData['meta_description'] = $validated['meta_description'] ?? null;
        $updateData['meta_keywords'] = $validated['meta_keywords'] ?? null;
        $updateData['meta_robot'] = $validated['meta_robot'] ?? null;
        $updateData['canonical_url'] = $validated['canonical_url'] ?? null;
        $updateData['custom_url'] = $validated['custom_url'] ?? null;
        $updateData['og_title'] = $validated['og_title'] ?? null;
        $updateData['og_description'] = $validated['og_description'] ?? null;
        $updateData['og_image'] = $this->resolveOgImage($request, $blog->og_image);

        $blog->update($updateData);

        return response()->json($blog);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        // Delete image from Cloudinary if it exists
        if ($blog->image && CloudinaryService::isCloudinaryUrl($blog->image)) {
            CloudinaryService::deleteImage($blog->image);
        }
        
        // Delete OG image from Cloudinary if it exists
        if ($blog->og_image && CloudinaryService::isCloudinaryUrl($blog->og_image)) {
            CloudinaryService::deleteImage($blog->og_image);
        }

        $blog->delete();

        return response()->json(null, 204);
    }

    private function normalizeOptionalFields(Request $request): void
    {
        $nullableFields = [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'meta_robot',
            'canonical_url',
            'custom_url',
            'json_ld',
            'og_title',
            'og_description',
            'og_image',
        ];

        foreach ($nullableFields as $field) {
            if ($request->has($field)) {
                $value = trim((string) $request->input($field));
                $request->merge([$field => $value === '' ? null : $value]);
            }
        }
    }

    private function resolveOgImage(Request $request, ?string $existing = null): ?string
    {
        if ($request->hasFile('og_image')) {
            // Delete old OG image from Cloudinary if it exists
            if ($existing && CloudinaryService::isCloudinaryUrl($existing)) {
                CloudinaryService::deleteImage($existing);
            }

            return CloudinaryService::uploadImage($request->file('og_image'), 'blogs_og');
        }

        if ($request->filled('og_image')) {
            return $request->og_image;
        }

        return $existing;
    }
}
