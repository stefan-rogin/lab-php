<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Post;
use App\Http\Resources\PostResource;
use App\Services\BlogClientService;

/**
 * Service class for fetching and persisting post from the external source.
 * 
 */
class PostService {

    // Web client for external blog
    private BlogClientService $client;

    /**
     * PostService constructor.
     *
     * @param string $url The URL to be used by the service.
     */
    public function __construct(BlogClientService $client) {
        $this->client = $client;
    }

    /**
     * Imports posts by getting posts from client service and persisting valid ones.
     */
    public function import(): bool {

        // Get posts from client
        $posts = $this->client->fetchPosts();

        // Import counters for reporting
        $stats = [
            'fetched' => 0,
            'valid' => 0,
            'imported' => 0,
        ];

        if (!$posts) {
            // Log warning if external service is unavailable, then return false.
            Log::warning('Failed to fetch posts, external service is unavailable.');
            return false;
        }

        $stats['fetched'] = $posts->count();

        // Ensure that posts are valid
        $validPosts = $posts->reject(fn (PostResource $post) => !self::isValidPost($post));
        $stats['valid'] = $validPosts->count();

        // Iterate through valid posts
        $validPosts->each(function (PostResource $post) use ($stats) {

            // FIXME: Move to persistence layer
            // Begin a transaction to avoid creating unnnecessary category records
            DB::beginTransaction();
            try {
                // Find or create the post's category
                $category = Category::firstOrCreate([
                    'name' => $post->resource['category'],
                ]);

                // Create post record if the post Id is not already stored
                $category->posts()->firstOrCreate([
                    'id' => $post->resource['id'],
                ], [
                    'title' => $post->resource['title'],
                    'content' => $post->resource['content'],
                    'author' => $post->resource['author'],
                    'date' => $post->resource['date'],
                    'category_id' => $category->id,
                ]);

                // Commit transaction    
                DB::commit();
                $stats['imported']++;
            } catch (Exception $e) {
                // Log failure 
                Log::error("Failed to store post: {$post['id']}", $e->getMessage());
                // then rollback current iteration.
                DB::rollBack();
            }
        });

        // Log stats
        Log::info("Import complete. Fetched: {$stats['fetched']}, Valid: {$stats['valid']}, Imported: {$stats['imported']}.");
        // Return true for success
        return true;

    }

    // FIXME: User form validators
    /**
     * Validation method for posts integrity.
     * 
     * @return bool Return true if the PostResource object is valid, false otherwise
     */
    public static function isValidPost(PostResource $post): bool {
        $validator = Validator::make($post->resource, [
            'id' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'required|string|max:255',
            'date' => 'required|date',
            'category' => 'required|string|max:255',
        ]);
        return !$validator->fails();
    }

}