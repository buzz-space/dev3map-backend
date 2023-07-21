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
use Botble\Statistic\Models\Developer;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;

Route::get("test", function () {
    $full_time = [];
    $part_time = [];
    $one_time = [];

    $contributors = array_unique(explode(",", implode(",", Contributor::where("chain", 4)->pluck("contributors")->toArray())));
    $repos = Repository::where("chain", 4)->get();
    $f = array_unique(explode(",", implode(",", Commit::where("chain", 4)
        ->where("exact_date", ">=", now()->addMonths(-3)->toDateString())->pluck("full_time")->toArray())));
    $p = array_unique(explode(",", implode(",", Commit::where("chain", 4)
        ->where("exact_date", ">=", now()->addMonths(-3)->toDateString())->pluck("part_time")->toArray())));
    $o = array_unique(explode(",", implode(",", Commit::where("chain", 4)
        ->where("exact_date", ">=", now()->addMonths(-3)->toDateString())->pluck("one_time")->toArray())));

    $f = array_filter($f, function ($row) use ($contributors){
        return in_array($row, $contributors);
    });

    $p = array_filter($p, function ($row) use ($f, $contributors) {
        return !in_array($row, $f) && in_array($row, $contributors);
    });

    $o = array_filter($o, function ($row) use ($f, $p, $contributors) {
        return !in_array($row, $p) && !in_array($row, $f) && in_array($row, $contributors);
    });

    $full_time = array_filter(array_unique($f));
    $part_time = array_filter(array_unique($p));
    $one_time = array_filter(array_unique($o));
//
    $developer = compact("full_time", "part_time", "one_time");
//
    $top = Pull::where("chain", 4)->groupBy("author")->selectRaw("COUNT(*) as total, author")->orderBy("total", "DESC")->first()->toArray();
//
    $total = Issue::where("chain", 4)->groupBy("chain")->selectRaw("chain, COUNT(*) as count, SUM(total_minute) as total")->first()->toArray();
//
    $issue_perform = number_format($total["total"] / $total["count"] / 60 / 24) . " day/issue (" . $total["total"] . "/" . $total["count"] . " minutes)";
//
    $pullCreator = array_unique(explode(",", implode(",",Pull::where("chain", 4)->pluck("author")->toArray())));

    $outbound = array_filter($pullCreator, function ($row) use ($contributors){
       return !in_array($row, $contributors);
    });

    $outboundPulls = Pull::whereIn("author", $outbound)->where("chain", 4)->count();

    $community_attribute = number_format($outboundPulls / count($outbound)) . " pull request/pull creator ($outboundPulls/" . count($outbound) . ")";

    dd(compact("developer", "top", "issue_perform", "community_attribute"));
});

Route::get("test1", function () {
    $url1 = "https://api.github.com/repos/aura-nw/cosmoscan-api/contributors";
    $url2 = "https://api.github.com/repos/everstake/cosmoscan-api/contributors";

    $data1 = array_column((array) json_decode(get_github_data($url1)), "login");
    $data2 = array_column((array) json_decode(get_github_data($url2)), "login");
    dd(compact("data1", "data2"));
});
