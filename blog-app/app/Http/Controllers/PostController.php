<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Services\PostService;

class PostController extends Controller
{
    public function fetchPosts(Request $request) {
        // TODO: move to config
        // TODO: Move logic to service
        $service = new PostService('https://api.vercel.app/blog');
        $data = $service->fetchPosts();
        if (is_array($data)) {
            foreach ($data as $post) {
                if (PostService::isValidPost($post)) {
                    Post::firstOrCreate([
                        'id' => $post['id'],
                    ], [
                        'title' => $post['title'],
                        'content' => $post['content'],
                        'created_at' => $post['date'],
                    ]);
                } else {
                    // Log skipped record
                }
            }
            return response()->json(['message' => 'Posts fetched.'], 200);    
        }
        return response()->json(['error' => 'Unable to fetch posts.'], 500);
    }
}
