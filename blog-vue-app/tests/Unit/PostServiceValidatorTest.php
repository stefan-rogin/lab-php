<?php

use App\Services\PostService;
use App\Http\Resources\PostResource;

uses(Tests\TestCase::class);

/**
 * Tests for the PostResource validator
 */

test('validates ok posts', function () {
    $validPost = new PostResource([
        'id' => 4,
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ]);
    expect(PostService::isValidPost($validPost))->toBeTrue();
});

test('invalidates posts missing required fields', function () {
    $invalidPosts = collect([[
        'id' => NULL, // No Id
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => '', // Empty title
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title', 
        // 'content' => 'Something', // Missing content
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title', 
        'content' => 'Something', 
        'author' => '', //Missing author
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => '', // Empty category
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => NULL, // Null date
    ]]);

    $invalidPosts
        ->mapInto(PostResource::class)
        ->each(fn (PostResource $post) => expect(PostService::isValidPost($post))->toBeFalse());
});

test('invalidates posts having miss-shaped fields', function () {
    $tooLong = fake()->text(500);

    $invalidPosts = collect([[
        'id' => 'a1', // Not a numeric Id
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => '-3', // Negative Id
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => $tooLong, // Too long title
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title', 
        'content' => false, // Not text content
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title', 
        'content' => 'Something',
        'author' => true, // Author bool
        'category' => 'Code',
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => $tooLong, // Too long category
        'date' => '2025-03-26',
    ], [
        'id' => 3,
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => 'Not a date', // Not a date
    ]]);

    $invalidPosts
        ->mapInto(PostResource::class)
        ->each(fn (PostResource $post) => expect(PostService::isValidPost($post))->toBeFalse());
});
