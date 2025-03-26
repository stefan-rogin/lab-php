<?php

namespace Tests\Feature;

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Post;

test('responds with success when empty', function () {
    $this->assertDatabaseEmpty('posts')
        ->get('/api')
        ->assertOk()
        ->assertExactJson([]);
});

test('fetches and stores valid posts', function() {
    $category = Category::factory()->create(['name' => 'Code']);
    $fakePosts = Post::factory()->count(3)->make()->toArray();
    $fakeResponsePosts = array_map(function($post) use ($category) {
        unset($post['category_id']);
        $post['category'] = $category->name;
        return $post;
    }, $fakePosts);

    Http::fake([
        'https://api.vercel.app/blog' => Http::response($fakeResponsePosts, 200),
    ]);

    $this->get('/api/fetchPosts')->assertOk();
    
    $this->assertDatabaseCount('posts', 3);
    foreach ($fakeResponsePosts as $fakePost) {
        $this->assertDatabaseHas('posts', [
            'id' => $fakePost['id'],
            'title' => $fakePost['title'],
            'content' => $fakePost['content'],
            'date' => $fakePost['date'],
            'category_id' => $category->id,
        ]);
    }
});

test('fetches and stores valid posts, skipping invalid posts', function() {
    $category = Category::factory()->create(['name' => 'Code']);
    $validPost = Post::factory()->make([
        'id' => '10',
        'title' => 'Valid Post',
        'content' => 'Something',
        'category' => $category->name,
        'date' => '2025-03-26',
    ]);
    $invalidPost = [
        'id' => '1000',
        'title' => 'Invalid Post',
        'content' => 'Something else',
        'category' => $category->name,
        'date' => 'Invalid Date',
    ];

    Http::fake([
        'https://api.vercel.app/blog' => Http::response(json_encode([$validPost, $invalidPost]), 200),
    ]);
    $this->get('/api/fetchPosts')->assertOk();

    $this->assertDatabaseCount('posts', 1)
        ->assertDatabaseHas('posts', [
            'id' => $validPost['id'],
            'title' => $validPost['title'],
            'content' => $validPost['content'],
            'date' => $validPost['date'],
            'category_id' => $category->id,
        ]);
});

test('responds with error when service fails', function() {
    Http::fake([
        'https://api.vercel.app/blog' => Http::response('', 500),
    ]);
    $this->get('/api/fetchPosts')->assertServerError();
});

test('responds with fetched posts', function() {
    $fakePosts = Post::factory()->count(5)->create();
    $this->assertDatabaseCount('posts', 5);
    $this->get('/api')
        ->assertOk()
        ->assertJson(fn ($json) => 
            $json->has(5)
            ->first(fn ($json) => $json
                ->whereAllType([
                    'id' => 'integer',
                    'title' => 'string',
                    'content' => 'string',
                    'date' => 'string',
                    'category_id' => 'integer',
                ])
            )
    );
});