<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/', [PostController::class, 'list']);
// TODO: change to post
Route::get('/fetchPosts', [PostController::class, 'fetchPosts']);