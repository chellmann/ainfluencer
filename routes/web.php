<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/svg/{post}', function (Post $post) {
    try {
        // Generiere das SVG
        $post->generateVector();

        // Lade das SVG aus dem Speicher
        $svgContent = Storage::get($post->svg);

        // Zeige das SVG in HTML mit 50 % Größe und rotem Rand
        return response($svgContent, 200, ['Content-Type' => 'image/svg+xml']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
})->name('svg.show');

Route::get('/posts/{post}', function (Post $post) {
    return view('posts.show', [
        'post' => $post,
    ]);
})->name('videoinput');
