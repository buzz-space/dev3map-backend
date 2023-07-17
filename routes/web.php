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

use Botble\Statistic\Models\Developer;

Route::get("test", function (){
    $full_time = array_filter(array_unique(explode(",", implode(",", Developer::where("chain", 4)->pluck("full_time")->toArray()))));
    $part_time = array_filter(array_unique(explode(",", implode(",", Developer::where("chain", 4)->pluck("part_time")->toArray()))));
    $one_time = array_filter(array_unique(explode(",", implode(",", Developer::where("chain", 4)->pluck("one_time")->toArray()))));

    $one_time = array_filter($one_time, function ($row) use ($full_time, $part_time){
       return !in_array($row, $part_time) &&  !in_array($row, $full_time) && !strpos($row,"@users.noreply.github.com");
    });

    $part_time = array_filter($part_time, function ($row) use ($full_time){
       return !in_array($row, $full_time) && !strpos($row,"@users.noreply.github.com");
    });

    $full_time = array_filter($full_time, function ($row){
       return !strpos($row,"@users.noreply.github.com");
    });

    dd(compact("full_time", "part_time", "one_time"));
});
