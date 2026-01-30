<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return 'web route works!';
});

Route::get('/storage/posts/{filename}', function ($filename) {
    $path = storage_path("app/public/posts/{$filename}");

    if (!file_exists($path)) {
        abort(404);
    }

    return Response::file($path);
});

