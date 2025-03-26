<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use App\Models\Post;

class PostController extends Controller
{
    public function fetchPosts() {
        // TODO: get from config
        $service = new PostService('https://api.vercel.app/blog');
        if ($service->fetchPosts()) {
            return response()->json(['message' => 'Posts fetched.'], 200);    
        }
        return response()->json(['error' => 'Unable to fetch posts.'], 500);
    }

    public function list() {
        return Post::all();
    }
}
