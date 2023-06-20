<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Statistic\Http\Controllers\API',
], function () {
    Route::get("chain-list", "StatisticController@chainList");
    Route::get("commit-info", "StatisticController@commitInfo");
    Route::get("developer-info", "StatisticController@developerInfo");
});
