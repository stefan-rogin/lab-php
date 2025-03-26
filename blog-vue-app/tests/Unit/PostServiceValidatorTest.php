<?php

use App\Services\PostService;

uses(Tests\TestCase::class);

test('validates ok posts', function () {
    $validPost = [
        'id' => 4,
        'title' => 'Valid title',
        'content' => 'Something',
        'author' => 'Sarah Johnson',
        'category' => 'Code',
        'date' => '2025-03-26',
    ];
    expect(PostService::isValidPost($validPost))->toBeTrue();
});

test('invalidates posts missing required fields', function () {
    $invalidPosts = [[
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
    ]];
    foreach ($invalidPosts as $invalidPost) {
        expect(PostService::isValidPost($invalidPost))->toBeFalse();    
    }
});

test('invalidates posts having miss-shaped fields', function () {
    $tooLong = fake()->text(500);

    $invalidPosts = [[
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
    ]];
    foreach ($invalidPosts as $invalidPost) {
        expect(PostService::isValidPost($invalidPost))->toBeFalse();    
    }
});
