<?php

namespace Botble\Statistic\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\Developer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatisticController extends BaseController
{
    public function chainList(Request $request, BaseHttpResponse $response)
    {
        $query = Chain::query();
        $categories = explode(',', $request->input("categories", ""));
        if (!empty($categories)) {
            foreach ($categories as $z){
                $query->where("categories", "like", "%$z%");
            }
        }
        return $response->setData($query->get());
    }

    public function chainInfo($id, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($id))
            return $response->setError()->setMessage("Chain not found!");

        return $response->setData($chain);
    }

    public function commitInfo(Request $request, BaseHttpResponse $response)
    {
        if ($chain = Chain::find($request->input("chain", 0))) {
            $data["total_commit"] = $chain->total_commit;
            $data["commit_chart"] = CommitChart::where("chain", $chain->id)
                ->selectRaw("`from`, total_commit, total_additions, total_deletions")
                ->orderBy("`from`", "ASC")
                ->get();
        }
        else {
            $data["total_commit"] = Chain::sum("total_commit");
            $data["commit_chart"] = CommitChart::groupByRaw("week, month, year")
                ->selectRaw("week, month, year, SUM(total_commit) as total_commit, SUM(total_additions) as total_additions, SUM(total_deletions) as total_deletions")
                ->orderBy("year", "ASC")->orderBy("month", "ASC")->orderBy("week", "ASC")
                ->get();
        }
        return $response->setData($data);
    }

    public function developerInfo(Request $request, BaseHttpResponse $response)
    {
        $data = [];
        if ($chain = Chain::find($request->input("chain", 0))) {
            $data["total_developer"] = $chain->total_developer;
            $data["total_full_time"] = $chain->total_full_time_developer;
            $data["total_part_time"] = $chain->total_part_time_developer;
            $data["total_one_time"] = $chain->total_one_time_developer;

            $year = Developer::where([
                ["chain", $chain->id],
                ["year", now()->year]
            ])->pluck("author")->toArray();
            $year = process_developer_string(implode(",", $year));
            foreach (["full_time", "part_time", "one_time"] as $type) {

                $data[$type] = [
                    "ath" => Developer::where("chain", $chain->id)->max("total_$type"),
                    "atl" => Developer::where("chain", $chain->id)->min("total_$type"),
                    "this_month" => ($devs = Developer::where([
                        ["chain", $chain->id],
                        ["month", now()->month],
                        ["year", now()->year]
                    ])->first()) ? $devs["total_$type"] : 0,
                    "this_year" => $year[$type]
                ];
            }

            $data["developer_chart"] = Developer::where("chain", $chain->id)
                ->select("month", "year", "total_developer", 'total_one_time', 'total_part_time', 'total_full_time')
                ->orderBy("year", "ASC")->orderBy("month", "ASC")
                ->get();

        }
        else {
            $data["total_developer"] = Chain::sum("total_developer");
            $data["total_full_time"] = Chain::sum("total_full_time_developer");
            $data["total_part_time"] = Chain::sum("total_part_time_developer");
            $data["total_one_time"] = Chain::sum("total_one_time_developer");

            $year = Developer::where("year", now()->year)->pluck("author")->toArray();
            $year = process_developer_string(implode(",", $year));
            foreach (["full_time", "part_time", "one_time"] as $type) {
                $data[$type] = [
                    "ath" => Developer::max("total_$type"),
                    "atl" => Developer::min("total_$type"),
                    "this_month" => ($devs = Developer::where([
                        ["month", now()->month],
                        ["year", now()->year]
                    ])->first()) ? $devs["total_$type"] : 0,
                    "this_year" => $year[$type]
                ];
            }

            $data["developer_chart"] = Developer::groupByRaw("month, year")
                ->selectRaw("month, year, SUM(total_developer) as total_developer, SUM(total_one_time) as total_one_time, SUM(total_part_time) as total_part_time, SUM(total_full_time) as total_full_time")
                ->orderBy("year", "ASC")->orderBy("month", "ASC")
                ->get();
        }

        return $response->setData($data);
    }

    public function getCategories(BaseHttpResponse $response)
    {
        $data = Chain::whereNotNull("categories")->pluck("categories")->toArray();
        $data = array_values(array_unique(explode(",", implode(",", $data))));
        $z = [];
        foreach ($data as $item){
            $found = Chain::where("categories", "like", "%$item%")->count();
            $z[] = [
                'name' => $item,
                'total' => $found
            ];
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
}
