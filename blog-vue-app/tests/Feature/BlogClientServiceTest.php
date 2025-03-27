<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Post;
use App\Services\BlogClientService;

/**
 * Tests for the blog client service
 */

test('fetches posts and deserializes response in a collection of PostResource', function () {
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
    $fetchedPosts = $client->fetchPosts();

    expect($fetchedPosts)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($fetchedPosts)->toHaveCount(3);
    $fetchedPosts->each(function ($post, $index) use ($fakePosts) {
        expect($post)->toBeInstanceOf(\App\Http\Resources\PostResource::class);
        expect($post['id'])->toEqual($fakePosts[$index]['id']);
        expect($post['title'])->toEqual($fakePosts[$index]['title']);
        expect($post['content'])->toEqual($fakePosts[$index]['content']);
        expect($post['author'])->toEqual($fakePosts[$index]['author']);
        expect($post['category'])->toEqual($fakePosts[$index]['category']);
        expect($post['date'])->toEqual($fakePosts[$index]['date']);
    });
});

test('returns null when external service is unavailable', function () {
    $POST_SERVICE_URL = config('services.post_service.url');

    Http::fake([
        $POST_SERVICE_URL => Http::response('', 500),
    ]);

    $client = new BlogClientService($POST_SERVICE_URL);
    expect($client->fetchPosts())->toBeNull();
});
