<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FetchPostsController;

// TODO: change to post
Route::get('/fetchPosts', [FetchPostsController::class, 'fetchPosts']);