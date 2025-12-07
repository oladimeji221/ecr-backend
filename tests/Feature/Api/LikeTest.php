<?php

namespace Tests\Feature\Api;

use App\Models\Blog;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_like_a_blog()
    {
        $blog = Blog::factory()->create();

        $response = $this->postJson('/api/blogs/' . $blog->id . '/likes');

        $response->assertStatus(201);

        $this->assertDatabaseHas('likes', [
            'blog_id' => $blog->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function test_cannot_like_a_blog_twice()
    {
        $blog = Blog::factory()->create();
        $blog->likes()->create(['user_id' => auth()->id()]);

        $response = $this->postJson('/api/blogs/' . $blog->id . '/likes');

        $response->assertStatus(422);
    }

    public function test_can_unlike_a_blog()
    {
        $blog = Blog::factory()->create();
        $blog->likes()->create(['user_id' => auth()->id()]);

        $response = $this->deleteJson('/api/blogs/' . $blog->id . '/likes');

        $response->assertStatus(204);

        $this->assertDatabaseMissing('likes', [
            'blog_id' => $blog->id,
            'user_id' => auth()->id(),
        ]);
    }
}
