<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Statistic\Http\Controllers\API',
], function () {
    Route::get("chain-list", "StatisticController@chainList");
    Route::get("chain/{id}", "StatisticController@chainInfo");
    Route::get("summary-info", "StatisticController@summaryInfo");
    Route::get("commit-chart", "StatisticController@getCommitChart");
    Route::get("developer-chart", "StatisticController@getDeveloperChart");
    Route::get("categories", "StatisticController@getCategories");
    Route::get("ranking", "StatisticController@ranking");
    Route::post("add-chain", "StatisticController@addChain");
});
