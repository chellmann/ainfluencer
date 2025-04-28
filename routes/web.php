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

Route::get('/posts/{post}/preview', function (Post $post) {
    $scale = 0.4;
    return '
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iframe Beispiel</title>
    <style>
        .scaled-iframe {
            width: 1080px; /* Breite des Iframes */
            height: 1920px; /* Höhe des Iframes */
            border: none; /* Kein Rahmen */
            transform: scale('.$scale.'); /* Verkleinern auf 50% */
            transform-origin: 0 0; /* Ursprung der Transformation */
        }
    </style>
</head>
<body>
    <div style="overflow: hidden; width: '.(1080*$scale). 'px; height: ' . (1920 * $scale) . 'px;">
        <iframe src="' . route('videoinput', $post) . '" class="scaled-iframe"></iframe>
    </div>
</body>
</html>';
})->name('preview');
