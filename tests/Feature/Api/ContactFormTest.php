<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use WithFaker;

    public function test_can_submit_contact_form()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'message' => $this->faker->paragraph,
        ];

        $response = $this->postJson('/api/contact', $data);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Your message has been sent successfully!']);
    }

    public function test_contact_form_validation()
    {
        $response = $this->postJson('/api/contact', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }
}
