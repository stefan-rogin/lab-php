<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use App\Services\BlogClientService;
use App\Models\Post;
use App\Models\Category;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;

/**
 * Controller class for /api endpoints.
 * 
 */
class PostController extends Controller {

    /**
     * Fetch a list of posts and respond with success or failure.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response having the operation result.
     */
    public function import(): JsonResponse {
        
        // Set external endpoint in services config
        $POST_SERVICE_URL = config('services.post_service.url');

        // Create client an importer instances
        $client = new BlogClientService($POST_SERVICE_URL);
        $service = new PostService($client);

        if ($service->import()) {
            // Respond with success
            return response()->json(['message' => 'Posts fetched.'], 200);    
        }
        return response()->json(['error' => 'Failed to fetch posts.'], 500);
    }

    /**
     * Respond with the list of stored posts.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the list of posts.
     */
    public function list(): JsonResponse {

        // Get all stored posts, eager loading category name
        $posts = Post::with('category')->get();
        // Respond with the serialized collection
        return PostResource::collection($posts)->response();
    }
}
