<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/', [PostController::class, 'list']);
Route::post('/import', [PostController::class, 'import']);