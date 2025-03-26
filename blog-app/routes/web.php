<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

Route::get('/', function () {
    $posts = Post::all();
    return view('welcome', ['posts' => $posts]);
});
