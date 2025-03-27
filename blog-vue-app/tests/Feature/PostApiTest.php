<?php

namespace Tests\Feature;

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Post;

/**
 * Tests for the API Controller
 */

test('responds with success when empty', function () {
    $this->assertDatabaseEmpty('posts')
        ->get('/api')
        ->assertOk()
        ->assertExactJson(['data' => []]);
});

test('responds with stored posts and category', function () {
    $fakePosts = Post::factory()->count(5)->create();
    $this->assertDatabaseCount('posts', 5);
    $this->get('/api')
        ->assertOk()
        ->assertJson(fn ($json) => 
            $json->has('data', 5)
    );
});

test('responds with success when fetching external posts', function () {
    $category = Category::factory()->create(['name' => 'Code']);
    $validPost = Post::factory()->make([
        'id' => '10',
        'title' => 'Valid Post',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => $category->name,
        'date' => '2025-03-26',
    ]);
    
    Http::fake([
        config('services.post_service.url') => Http::response(json_encode([$validPost]), 200),
    ]);
    $this->post('/api/fetchPosts')->assertOk();

    $this->assertDatabaseCount('posts', 1)
        ->assertDatabaseHas('posts', [
            'id' => $validPost['id'],
            'title' => $validPost['title'],
            'content' => $validPost['content'],
            'author' => $validPost['author'],
            'date' => $validPost['date'],
            'category_id' => $category->id,
        ]);
});

test('responds with error when external service fails', function () {
    Http::fake([
        config('services.post_service.url') => Http::response('', 500),
    ]);
    $this->post('/api/fetchPosts')->assertServerError();
});
