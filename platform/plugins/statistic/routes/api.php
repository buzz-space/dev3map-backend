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
    Route::get("chain-repository/{id}", "StatisticController@getChainRepository");
    Route::get("chain-developer/{id}", "StatisticController@getTopDeveloper");
    Route::get("performance/{id}", "StatisticController@getPerformance");
    Route::get("developer/{login}", "StatisticController@getContributorInfo");
    Route::get("developer-activity/{login}", "StatisticController@getContributorActivity");
    Route::get("developer-contribution/{login}", "StatisticController@getDeveloperContribution");
    Route::get("developer-repository/{login}", "StatisticController@getContributorRepositories");
    Route::get("developer-statistic/{login}", "StatisticController@getContributorStatistic");
    Route::post("add-chain", "StatisticController@addChain");
});
