<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Post;
use App\Http\Resources\PostResource;

class PostService {

    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function fetchPosts(): bool {
        $response = Http::acceptJson()->get($this->url);
        if ($response->successful()) {
            $data = $response
                ->collect()
                ->mapInto(PostResource::class)
                ->filter(fn (PostResource $post) => self::isValidPost($post));

            $data->each(function (PostResource $post) {
                DB::beginTransaction();
                try {
                    $category = Category::firstOrCreate([
                        'name' => $post->resource['category'],
                    ]);
                    $category->posts()->firstOrCreate([
                        'id' => $post->resource['id'],
                    ], [
                        'title' => $post->resource['title'],
                        'content' => $post->resource['content'],
                        'author' => $post->resource['author'],
                        'date' => $post->resource['date'],
                        'category_id' => $category->id,
                    ]);    
                    DB::commit();
                } catch (Exception $e) {
                    Log::error('Failed to store post: '.$post['id'], $e->getMessage());
                    DB::rollBack();
                }
            });
            return true;
        } else {
            Log::warning('Failed to fetch posts, external service is unavailable.');
        }
        return false;
    }

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