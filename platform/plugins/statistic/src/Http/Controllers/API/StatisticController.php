<?php

namespace Botble\Statistic\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
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

        $firstCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "ASC")->first();
        $lastCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "DESC")->first();
        $dateFirstCommit = Carbon::createFromTimestamp(strtotime($firstCommit->exact_date));
        $dateLastCommit = Carbon::createFromTimestamp(strtotime($lastCommit->exact_date));
        $diff = $dateFirstCommit->diffInMonths($dateLastCommit) + ($dateFirstCommit->day > $dateLastCommit->day ? 2 : 1 );
        $commitAnalytic = [];
        for ($i = 0; $i < $diff; $i++) {
            $exactMonth = (clone $dateFirstCommit)->addMonths($i);
            $total_commit = Commit::where("chain", $chain->id)
                ->where("exact_date", ">=", $exactMonth->firstOfMonth()->toDateTimeString())
                ->where("exact_date", "<", $exactMonth->endOfMonth()->toDateTimeString())
                ->sum("total_commit");
            $commitAnalytic[] = [
                "year" => $exactMonth->year,
                "month" => $exactMonth->month,
                "total_commit" => $total_commit
            ];
        }

        $data["commit_chart"] = $commitAnalytic;
        return $response->setData($data);
    }

    // TODO: Developer api
}
