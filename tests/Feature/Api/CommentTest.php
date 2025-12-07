<?php

namespace Tests\Feature\Api;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_get_all_comments_for_a_blog()
    {
        $blog = Blog::factory()->create();
        Comment::factory()->count(3)->create(['blog_id' => $blog->id]);

        $response = $this->getJson('/api/blogs/' . $blog->slug . '/comments');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_a_comment_for_a_blog()
    {
        $blog = Blog::factory()->create();
        $data = [
            'content' => $this->faker->paragraph,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
        ];

        $response = $this->postJson('/api/blogs/' . $blog->slug . '/comments', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['content' => $data['content']]);
    }

    public function test_can_get_a_comment()
    {
        $comment = Comment::factory()->create();

        $response = $this->getJson('/api/comments/' . $comment->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['content' => $comment->content]);
    }

    public function test_can_update_a_comment()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $data = [
            'content' => $this->faker->paragraph,
        ];

        $response = $this->putJson('/api/comments/' . $comment->id, $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('comments', $data);
    }

    public function test_can_delete_a_comment()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/comments/' . $comment->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_cannot_update_another_users_comment()
    {
        $comment = Comment::factory()->create();
        $data = [
            'content' => $this->faker->paragraph,
        ];

        $response = $this->putJson('/api/comments/' . $comment->id, $data);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_another_users_comment()
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson('/api/comments/' . $comment->id);

        $response->assertStatus(403);
    }
}
