<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Blog\Http\Controllers\API',
], function () {

    Route::prefix("blog")->group(function () {
        Route::get('posts', 'PostController@getPosts');
        Route::get('post/{slug}', 'PostController@findBySlug');
        Route::get("author/{name}", 'PostController@getAuthor');
    });

    Route::get("categories", "PostController@getCategories");
});
