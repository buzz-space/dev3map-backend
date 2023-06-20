<?php

namespace Botble\Statistic\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Developer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticController extends BaseController
{
    public function chainList(BaseHttpResponse $response)
    {
        return $response->setData(Chain::all());
    }

    public function commitInfo(Request $request, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($request->input("chain", 0)))
            return $response->setError()->setMessage("Chain not found!");

        $data["total_commit"] = $chain->total_commits;
        $data["commit_chart"] = Developer::where("chain", $chain->id)->select("month", "year", "total_commit")->get();
        return $response->setData($data);
    }

    public function developerInfo(Request $request, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($request->input("chain", 0)))
            return $response->setError()->setMessage("Chain not found!");

        $devs = Developer::where("chain", $chain->id)->pluck("author")->toArray();
        $info = get_developer_type($devs);

        $data["total_developer"] = $info["total_developer"];
        $data["total_ft_developer"] = $info["full_time"];
        $data["total_ml_developer"] = $info["full_time"] + $info["part_time"];

        $data["chart_developer_type"] = Developer::where("chain", $chain->id)
            ->select("month", "year", "total_full_time", "total_part_time", "total_one_time")
            ->get();

        $data["chart_all"] = Developer::where("chain", $chain->id)
            ->select("month", "year", "total_developer")
            ->get();

        $devsThisYear = Developer::where("chain", $chain->id)
            ->where("year", now()->year)->pluck("author")->toArray();
        $infoYear = get_developer_type($devsThisYear);

        $devsThisMonth = Developer::where("chain", $chain->id)
            ->where("year", now()->year)->where("month", now()->month)
            ->first();

        $data["full_time"] = [
            "all_time" => $info["full_time"],
            "this_year" => $infoYear["full_time"],
            "this_month" => $devsThisMonth["total_full_time"],
        ];

        $data["part_time"] = [
            "all_time" => $info["part_time"],
            "this_year" => $infoYear["part_time"],
            "this_month" => $devsThisMonth["total_part_time"],
        ];

        $data["one_time"] = [
            "all_time" => $info["one_time"],
            "this_year" => $infoYear["one_time"],
            "this_month" => $devsThisMonth["total_one_time"],
        ];

        return $response->setData($data);
    }
}
