<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/', [PostController::class, 'list']);
// TODO: change to post
Route::post('/fetchPosts', [PostController::class, 'fetchPosts']);