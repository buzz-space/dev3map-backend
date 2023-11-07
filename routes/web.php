<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\DeveloperStatistic;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;

Route::get("test", function () {
    $arr = [1];
    dd($arr + [2]);
});

Route::get("test1", function () {
    $developers = Commit::where("chain", 4)->where("exact_date", ">=", (clone now()->addDays(-3))->addMonths(-3)->startOfMonth())->get()->toArray();
    dd(array_column($developers, "full_time"));
});
