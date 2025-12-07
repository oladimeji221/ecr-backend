<?php

namespace Tests\Feature\Api;

use App\Models\Blog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Blog::factory()->count(3)->create();
    }

    public function test_can_get_all_blogs()
    {
        $response = $this->getJson('/api/blogs');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_a_blog()
    {
        Mail::fake();
        Storage::fake('public');

        $category = Category::factory()->create();
        $data = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'category_id' => $category->id,
            'status' => 'published',
            'image' => UploadedFile::fake()->image('blog.jpg'),
        ];

        $response = $this->postJson('/api/blogs', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $data['title']]);

        $blog = Blog::first();
        $this->assertDatabaseHas('blogs', ['title' => $data['title']]);
        Storage::disk('public')->assertExists($blog->image);
    }

    public function test_can_get_a_blog()
    {
        $blog = Blog::factory()->create();

        $response = $this->getJson('/api/blogs/' . $blog->slug);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $blog->title]);
    }

    public function test_can_update_a_blog()
    {
        $blog = Blog::factory()->create();
        $category = Category::factory()->create();

        $data = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'category_id' => $category->id,
            'status' => 'published',
        ];

        $response = $this->putJson('/api/blogs/' . $blog->slug, $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $data['title']]);

        $this->assertDatabaseHas('blogs', ['title' => $data['title']]);
    }

    public function test_can_delete_a_blog()
    {
        $blog = Blog::factory()->create();

        $response = $this->deleteJson('/api/blogs/' . $blog->slug);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }
}
