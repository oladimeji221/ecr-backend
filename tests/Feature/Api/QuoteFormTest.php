<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuoteFormTest extends TestCase
{
    use WithFaker;

    public function test_can_submit_quote_form()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'service' => $this->faker->word,
            'message' => $this->faker->paragraph,
        ];

        $response = $this->postJson('/api/quote', $data);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Your quote request has been sent successfully!']);
    }

    public function test_quote_form_validation()
    {
        $response = $this->postJson('/api/quote', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'service', 'message']);
    }
}
