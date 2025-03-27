<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use \Illuminate\Support\Collection;
use App\Http\Resources\PostResource;

class BlogClientService {

    // External service URL
    private string $url;

    // Contructor
    public function __construct(string $url) {
        $this->url = $url;
    }

    /**
     * Fetch posts from the external service.
     * 
     * @return \Illuminate\Support\Collection<PostResource>|null
     */
    public function fetchPosts(): ?Collection {

        // Get posts from external web service
        $response = Http::acceptJson()->get($this->url);

        if ($response->successful()) {
            // Deserialize response in a collection of PostResource
            return $response->collect()->mapInto(PostResource::class);
        }

        // Return NULL when unsuccessful
        return null;
    }
}