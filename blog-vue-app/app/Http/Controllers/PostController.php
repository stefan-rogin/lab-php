<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use App\Models\Post;
use App\Models\Category;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function fetchPosts(): JsonResponse {
        
        $POST_SERVICE_URL = config('services.post_service.url');

        $service = new PostService($POST_SERVICE_URL);
        if ($service->fetchPosts()) {
            return response()->json(['message' => 'Posts fetched.'], 200);    
        }
        return response()->json(['error' => 'Failed to fetch posts.'], 500);
    }

    public function list(): JsonResponse {
        $posts = Post::with('category')->get();
        return PostResource::collection($posts)->response();
    }
}
