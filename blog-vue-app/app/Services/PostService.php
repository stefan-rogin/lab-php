<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Post;

class PostService {

    // TODO: Url class available?
    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function fetchPosts(): bool {
        $response = Http::acceptJson()->get($this->url);
        if ($response->successful()) {
            $data = $response->json();
            if (is_array($data)) {
                foreach ($data as $post) {
                    if (self::isValidPost($post)) {
                        DB::beginTransaction();
                        try {
                            $category = Category::firstOrCreate([
                                'name' => $post['category'],
                            ]);
                            $category->posts()->firstOrCreate([
                                'id' => $post['id'],
                            ], [
                                'title' => $post['title'],
                                'content' => $post['content'],
                                'author' => $post['author'],
                                'date' => $post['date'],
                                'category_id' => $category->id,
                            ]);    
                            DB::commit();
                        } catch (Exception $e) {
                            Log::error('Failed to store post: '.$post['id'], $e->getMessage());
                            DB::rollBack();
                        }
                    } else {
                        Log::notice('Invalid post found: '.$post['id']);
                    }
                }
                return true;    
            }
        } else {
            Log::warning('Failed to fetch posts.');
        }
        return false;
    }

    public static function isValidPost($post): bool {
        $validator = Validator::make($post, [
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