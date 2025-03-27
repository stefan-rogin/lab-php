<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Post;
use App\Services\PostService;
use App\Services\BlogClientService;

/**
 * Tests for the post importer service
 */

test('fetches and stores valid posts, then returns true', function () {
    $POST_SERVICE_URL = config('services.post_service.url');

    $category = Category::factory()->create(['name' => 'Code']);
    $fakePosts = Post::factory()
        ->count(3)
        ->make()
        ->map(fn ($post) => array_merge($post->toArray(), ['category' => $category->name]));

    Http::fake([
        $POST_SERVICE_URL => Http::response($fakePosts, 200),
    ]);

    $client = new BlogClientService($POST_SERVICE_URL);
    $service = new PostService($client);
    $this->assertTrue($service->import());

    $this->assertDatabaseCount('posts', 3);
    $fakePosts->each(fn ($post) => $this->assertDatabaseHas('posts', [
        'id' => $post['id'],
        'title' => $post['title'],
        'content' => $post['content'],
        'author' => $post['author'],
        'date' => $post['date'],
        'category_id' => $category->id,
    ]));
});

test('fetches and stores valid posts, skipping invalid posts, then returns true', function () {
    $POST_SERVICE_URL = config('services.post_service.url');
    
    $category = Category::factory()->create(['name' => 'Code']);
    $validPost = Post::factory()->make([
        'id' => '10',
        'title' => 'Valid Post',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => $category->name,
        'date' => '2025-03-26',
    ]);
    $invalidPosts = [[
        'id' => '1000',
        'title' => 'Invalid Post',
        'content' => 'Something else',
        'author' => 'Sarah Johnson',
        'category' => $category->name,
        'date' => 'Invalid Date',
    ], [
        'id' => '1000',
        'title' => 'Incomplete Post',
    ]];

    Http::fake([
        $POST_SERVICE_URL => Http::response(json_encode([$validPost, $invalidPosts]), 200),
    ]);

    $client = new BlogClientService($POST_SERVICE_URL);
    $service = new PostService($client);

    $this->assertTrue($service->import());

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

test('returns false when external service fails', function () {
    $POST_SERVICE_URL = config('services.post_service.url');

    Http::fake([
        $POST_SERVICE_URL => Http::response('', 500),
    ]);

    $client = new BlogClientService($POST_SERVICE_URL);
    $service = new PostService($client);

    $this->assertFalse($service->import());
});
