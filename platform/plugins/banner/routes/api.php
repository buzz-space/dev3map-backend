<?php

Route::group([
    'middleware' => 'api',
    'prefix'     => 'api/v1/banner',
    'namespace'  => 'Botble\Banner\Http\Controllers\API',
], function () {
    Route::get("/", "BannerController@index");
});
