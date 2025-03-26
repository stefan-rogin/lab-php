<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use App\Models\Post;
use App\Models\Category;

class PostController extends Controller
{
    public function fetchPosts() {
        // TODO: get from config
        $service = new PostService('https://api.vercel.app/blog');
        if ($service->fetchPosts()) {
            return response()->json(['message' => 'Posts fetched.'], 200);    
        }
        return response()->json(['error' => 'Failed to fetch posts.'], 500);
    }

    public function list() {
        $posts = Post::with('category')->get();
        $transformedPosts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'author' => $post->author,
                'date' => $post->date,
                'category' => $post->category->name,
            ];
        });
        return response()->json($transformedPosts);
    }
}
