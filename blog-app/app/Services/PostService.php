<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PostService {

    // TODO: Url class available?
    private string $url;

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function fetchPosts() {
        $response = Http::acceptJson()->get($this->url);
        if ($response->successful()) {
            return $response->json();
        } else {
            // TODO: Log or throw
        }
        return [];
    }

    public static function isValidPost($post): bool {
        $validator = Validator::make($post, [
            'id' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'date' => 'required|date',
        ]);
        return !$validator->fails();
    }

}