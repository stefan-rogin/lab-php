<?php

use Illuminate\Support\Facades\Route;

// TODO: change to post
Route::get('/loadPosts', function() {
    return response()->json(['message' => 'helloo'], 200);
});