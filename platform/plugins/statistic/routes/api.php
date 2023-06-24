<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Statistic\Http\Controllers\API',
], function () {
    Route::get("chain-list", "StatisticController@chainList");
    Route::get("chain/{id}", "StatisticController@chainInfo");
    Route::get("commit-info", "StatisticController@commitInfo");
    Route::get("developer-info", "StatisticController@developerInfo");
    Route::get("categories", "StatisticController@getCategories");
    Route::get("ranking", "StatisticController@ranking");
});
