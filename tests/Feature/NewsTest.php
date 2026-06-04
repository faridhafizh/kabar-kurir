<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page()
    {
        Article::create([
            'title' => 'Test Article',
            'source' => 'Test Source',
            'description' => 'Test Description',
            'published_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
