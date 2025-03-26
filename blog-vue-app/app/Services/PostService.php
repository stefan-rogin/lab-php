<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
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
                        DB::transaction(function() use ($post) {
                            $category = Category::firstOrCreate([
                                'name' => $post['category'],
                            ]);
                            // TODO: Handle same id but different categories?
                            $category->posts()->firstOrCreate([
                                'id' => $post['id'],
                            ], [
                                'title' => $post['title'],
                                'content' => $post['content'],
                                'date' => $post['date'],
                                'category_id' => $category->id,
                            ]);    
                        });
                    } else {
                        // Log skipped record
                    }
                }
                return true;    
            }
        } else {
            // TODO: Log or throw
        }
        return false;
    }

    public static function isValidPost($post): bool {
        $validator = Validator::make($post, [
            'id' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'content' => 'required|string',
            'date' => 'required|date',
        ]);
        return !$validator->fails();
    }

}