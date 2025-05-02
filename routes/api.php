<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateOnceWithBasicAuth;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/testauth', function () {
    return response()->json(['message' => 'Authenticated successfully']);
})->middleware(AuthenticateOnceWithBasicAuth::class);

//generate a post resource to save a new post via json, validate the request
Route::post('/post', function (Request $request) {
    $validated = $request->validate([
        'brand_id' => 'required|exists:brands,id',
        'text' => 'required|string',
        'author' => 'nullable|string',
        'caption' => 'required|string',
        'image_prompt' => 'nullable|string',
        'font_color' => 'nullable|string',
        'font_size' => 'nullable|integer',
        'font_style' => 'nullable|string',
    ]);

    $post = Post::create($validated);

    return response()->json($post, 201);
})->name('posts.store')->middleware(AuthenticateOnceWithBasicAuth::class);
