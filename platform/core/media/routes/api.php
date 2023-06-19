<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix'     => 'api/v1/media',
    'namespace'  => 'Botble\Media\Http\Controllers\API',
], function () {
    Route::post("create", "MediaController@create");
});

