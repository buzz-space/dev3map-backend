<?php

namespace Botble\Statistic\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Developer;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatisticController extends BaseController
{
    public function chainList(Request $request, BaseHttpResponse $response)
    {
        $query = Chain::where("id", 4);
        $categories = explode(',', $request->input("categories", ""));
        if (!empty($categories)) {
            foreach ($categories as $z){
                $query->where("categories", "like", "%$z%");
            }
        }
        $data = $query->select(
            'id',
            'name',
            'github_prefix',
            'categories',
            'avatar',
            "subscribers",
            'website',
            "rising_star",
            "ibc_astronaut",
            "seriousness"
        )->get();
        return $response->setData($this->customDataChain($data));
    }

    public function chainInfo($prefix, BaseHttpResponse $response)
    {
        if (!$chain = Chain::where("github_prefix", $prefix)->first())
            return $response->setError()->setMessage("Chain not found!");

        return $response->setData($chain);
    }

    public function commitInfo(Request $request, BaseHttpResponse $response)
    {
        if ($chain = Chain::find($request->input("chain", 0))) {
            $data["total_commit"] = $chain->total_commit;
            $data["commit_chart"] = CommitChart::where("chain", $chain->id)
                ->selectRaw("week, month, year, total_commit, total_additions, total_deletions")
                ->orderBy("year", "DESC")->orderBy("month", "DESC")->orderBy("week", "ASC")
                ->take(62)
                ->get();
        }
        else {
            $data["total_commit"] = Chain::sum("total_commit");
            $data["commit_chart"] = CommitChart::groupByRaw("week, month, year")
                ->selectRaw("week, month, year, SUM(total_commit) as total_commit, SUM(total_additions) as total_additions, SUM(total_deletions) as total_deletions")
                ->orderBy("year", "DESC")->orderBy("month", "DESC")->orderBy("week", "ASC")
                ->take(62)
                ->get();
        }
        return $response->setData($data);
    }

    public function developerInfo(Request $request, BaseHttpResponse $response)
    {
        $data = [];
        $chain = Chain::find(18);
//        if ($chain = Chain::find($request->input("chain", 0))) {
//            $data["total_developer"] = $chain->total_developer;
//            $data["total_full_time"] = $chain->total_full_time_developer;
//            $data["total_part_time"] = $chain->total_part_time_developer;
//            $data["total_one_time"] = $chain->total_one_time_developer;

//            $year = Developer::where([
//                ["chain", $chain->id],
//                ["day", ">=", now()->firstOfYear()->toDateTimeString()],
//                ["day", "<=", now()->toDateTimeString()]
//            ])->pluck("author")->toArray();
//            foreach (["full_time", "part_time", "one_time"] as $type) {
//
//                $data[$type] = [
//                    "ath" => Developer::where("chain", $chain->id)->max("total_$type"),
//                    "atl" => Developer::where("chain", $chain->id)->min("total_$type"),
//                    "this_month" => ($devs = Developer::where([
//                        ["chain", $chain->id],
//                        ["day", ">=", now()->firstOfMonth()->toDateTimeString()],
//                        ["day", "<=", now()->toDateTimeString()]
//                    ])->first()) ? $devs["total_$type"] : 0,
//                    "this_year" => $year[$type]
//                ];
//            }

            $data["developer_chart"] = Developer::where("chain", $chain->id)
                ->select("day", "total_developer", 'total_one_time', 'total_part_time', 'total_full_time')
                ->orderBy("day", "ASC")
                ->get();

//        }
//        else {
//            $data["total_developer"] = Chain::sum("total_developer");
//            $data["total_full_time"] = Chain::sum("total_full_time_developer");
//            $data["total_part_time"] = Chain::sum("total_part_time_developer");
//            $data["total_one_time"] = Chain::sum("total_one_time_developer");
//
//            $year = Developer::where("year", now()->year)->pluck("author")->toArray();
//            $year = process_developer_string(implode(",", $year));
//            foreach (["full_time", "part_time", "one_time"] as $type) {
//                $data[$type] = [
//                    "ath" => Developer::max("total_$type"),
//                    "atl" => Developer::min("total_$type"),
//                    "this_month" => ($devs = Developer::where([
//                        ["month", now()->month],
//                        ["year", now()->year]
//                    ])->first()) ? $devs["total_$type"] : 0,
//                    "this_year" => $year[$type]
//                ];
//            }
//
//            $data["developer_chart"] = Developer::groupByRaw("month, year")
//                ->where("year", ">=", now()->year - 5)
//                ->selectRaw("month, year, SUM(total_developer) as total_developer, SUM(total_one_time) as total_one_time, SUM(total_part_time) as total_part_time, SUM(total_full_time) as total_full_time")
//                ->orderBy("year", "ASC")->orderBy("month", "ASC")
//                ->get();
//        }

        return $response->setData($data);
    }

    public function getCategories(Request $request, BaseHttpResponse $response)
    {
        $data = Chain::whereNotNull("categories")->pluck("categories")->toArray();
        $data = array_values(array_unique(explode(",", implode(",", $data))));
        sort($data);
        $additionalData = $request->has("with_data");
        $z = [];
        foreach ($data as $item){
            $chains = Chain::where("categories", "like", "%$item%")->select("id", "name", "github_prefix", "avatar")->get();
            $row = [
                'name' => $item,
                'total' => count($chains)
            ];
            if ($additionalData)
                $row["chain"] = $chains;
            $z[] = $row;
        }
        return $response->setData($z);
    }

    public function ranking(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            'type' => "required|in:rising_star,ibc_astronaut,seriousness"
        ]);

        if ($validator->fails())
            return $response->setError()->setMessage(processValidators($validator->errors()->toArray()));

        $type = $request->input("type");
        $data = Chain::orderBy($type, "DESC")->take(10)->get();
        return $response->setData($data);
    }

    private function customDataChain($chains, $filter = 24)
    {
        foreach ($chains as $chain){
            $commits = Commit::where([
                ["chain", $chain->id],
                ["exact_date", "<", now()->addHours(-1 * $filter)]
            ])->get()->toArray();
            //commit
            $chain->total_commit = array_sum(array_column($commits, "total_commit"));
            //developer
            $developers = Commit::where([
                ["chain", $chain->id],
                ["exact_date", "<", now()->addHours(-1 * $filter)],
                ["exact_date", ">=", now()->addHours(-1 * $filter)->addMonths(-6)]
            ])->get()->toArray();
            $contributors = unique_name(Contributor::where("chain", $chain->id)->pluck("contributors")->toArray());
            $fullTime = unique_name(array_column($developers, "full_time"));
            $fullTime = array_filter($fullTime, function ($c) use ($contributors){
               return !empty($c) && in_array($c, $contributors);
            });
            $partTime = unique_name(array_column($developers, "part_time"));
            $partTime = array_filter($partTime, function ($c) use ($contributors, $fullTime){
                return !empty($c) && in_array($c, $contributors) && !in_array($c, $fullTime);
            });
            $chain->active_developer = count($fullTime) + count($partTime);
//            $chain->full_time_developer = $fullTime;
//            $chain->part_time_developer = $partTime;
            //repos
            $chain->total_repository = Repository::where("chain", $chain->id)
                ->where("created_date", "<", now()->addHours(-1 * $filter))->count();
            //issue
            $chain->total_issue_solved = Issue::where("chain", $chain->id)
                ->where("open_date", "<", now()->addHours(-1 * $filter))->count();
            //pull
            $chain->total_pull_merged = Pull::where("chain", $chain->id)
                ->where("created_date", "<", now()->addHours(-1 * $filter))->count();
        }

        return $chains;
    }
}
