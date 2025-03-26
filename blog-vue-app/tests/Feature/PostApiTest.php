<?php

namespace Tests\Feature;

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Http;
use App\Models\Post;

test('responds with success when empty', function () {
    $this->assertDatabaseEmpty('posts');
    $response = $this->get('/api');
    expect($response->status())->toBe(200);
    expect($response->content())->toBe('[]');
});

test('fetches and stores valid posts', function() {
    $fakePosts = Post::factory()->count(3)->make();
    Http::fake([
        'https://api.vercel.app/blog' => Http::response(json_encode($fakePosts), 200),
    ]);
    $response = $this->get('/api/fetchPosts');
    $response->assertStatus(200);
    $this->assertDatabaseCount('posts', 3);
});

test('fetches and stores valid posts, skipping invalid posts', function() {
    $validPost = Post::factory()->make();
    $invalidPost = [
        'id' => '1000',
        'title' => 'Invalid Post',
        'content' => "",
        'date' => "Invalid Date"
    ];
    $fakePosts = [$validPost, $invalidPost];
    Http::fake([
        'https://api.vercel.app/blog' => Http::response(json_encode($fakePosts), 200),
    ]);
    $response = $this->get('/api/fetchPosts');
    $response->assertStatus(200);
    $this->assertDatabaseCount('posts', 1);
});

test('responds with error when service fails', function() {
    Http::fake([
        'https://api.vercel.app/blog' => Http::response('', 500),
    ]);
    $response = $this->get('/api/fetchPosts');
    $response->assertStatus(500);
});

test('responds with fetched posts', function() {
    $fakePosts = Post::factory()->count(5)->create();
    $this->assertDatabaseCount('posts', 5);
    $response = $this->get('/api');
    $response->assertStatus(200);
    $response->assertJson(fn ($json) => 
        $json->has(5)
        ->first(fn ($json) => $json
            ->whereAllType([
                'id' => 'integer',
                'title' => 'string',
                'content' => 'string',
                'date' => 'string',
            ])
        )
    );
});