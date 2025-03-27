<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Post;
use App\Http\Resources\PostResource;

/**
 * Service class for fetching and persisting post from the external source.
 * 
 */
class PostService {

    // External endpoint URL property.
    private string $url;


    /**
     * PostService constructor.
     *
     * @param string $url The URL to be used by the service.
     */
    public function __construct(string $url) {
        $this->url = $url;
    }

    /**
     * Fetches posts from the external data source and persists them.
     * 
     * @return bool Returns true if posts are successfully fetched, false otherwise.
     */
    public function fetchPosts(): bool {

        // Call external service
        $response = Http::acceptJson()->get($this->url);

        if ($response->successful()) {

            // Deserialize response as PostResource collection, then filter out invalid posts
            $data = $response
                ->collect()
                ->mapInto(PostResource::class)
                ->filter(fn (PostResource $post) => self::isValidPost($post));

             // Iterate through valid posts   
            $data->each(function (PostResource $post) {

                // Begin a transaction to avoid creating unnnecessary category records
                DB::beginTransaction();
                try {
                    // Find or create the post's category
                    $category = Category::firstOrCreate([
                        'name' => $post->resource['category'],
                    ]);

                    // Create post record if the post id is not already stored
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
                } catch (Exception $e) {
                    // Log failure 
                    Log::error('Failed to store post: '.$post['id'], $e->getMessage());
                    // then rollback cuurrent iteration.
                    DB::rollBack();
                }
            });

            // Return true for success
            return true;
        } else {
            // Log warning if external service is unavailable, then return false.
            Log::warning('Failed to fetch posts, external service is unavailable.');
        }
        return false;
    }

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