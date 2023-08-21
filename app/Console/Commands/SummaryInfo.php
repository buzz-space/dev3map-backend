<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainInfo;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Developer;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SummaryInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $day = Carbon::createFromTimestamp(strtotime("2023-08-18"));
        foreach (Chain::orderBy("id", "ASC")->get() as $chain){
            if ($chain->id != 4) continue;
            echo "Chain name: " . $chain->name . PHP_EOL;
            $range = [
                0 => "0",
                24 => "24_hours",
                (24 * 7) => "7_days",
                (24 * 30) => "30_days"
            ];
            foreach ($range as $filter => $range_name){
                echo "Range: $filter-$range_name" . PHP_EOL;
                $info = ChainInfo::where([
                    ["chain", $chain->id],
                    ["range", $range_name]
                ])->first();
                if (!$info){
                    $info = new ChainInfo();
                    $info->chain = $chain->id;
                    $info->range = $range_name;
                    $info->save();
                }
                $commits = Commit::where([
                    ["chain", $chain->id],
                    ["exact_date", "<", (clone $day)->addHours(-1 * $filter)]
                ])->get()->toArray();
                //commit
                $info->total_commits = array_sum(array_column($commits, "total_commit"));
                //developer (6 month range)
                $query =  Commit::where("chain", $chain->id);
                if ($filter == 0)
                    $query->where([
                        ["exact_date", "<", (clone $day)->addHours(-1 * $filter)],
                        ["exact_date", ">=", (clone $day)->addHours(-1 * $filter)->addMonths(-3)]
                    ]);
                else
                    $query->where("exact_date", ">=", (clone $day)->addHours(-1 * $filter));
                $developers = $query->get()->toArray();
                $contributors = unique_name(Repository::where("chain", $chain->id)->pluck("total_contributor")->toArray());
                $fullTime = unique_name(array_column($developers, "full_time"));
                $fullTime = array_filter($fullTime, function ($c) use ($contributors){
                    return !empty($c) && in_array($c, $contributors);
                });
                $partTime = unique_name(array_column($developers, "part_time"));
                $partTime = array_filter($partTime, function ($c) use ($contributors, $fullTime){
                    return !empty($c) && in_array($c, $contributors) && !in_array($c, $fullTime);
                });
//            $info->total_developer = count($fullTime) + count($partTime);
                $info->full_time = implode(",", $fullTime);
                $info->part_time = implode(",", $partTime);
                $info->full_time_developer = count($fullTime);
                $info->part_time_developer = count($partTime);
                //repos
                $info->total_repository = Repository::where("chain", $chain->id)
                    ->where("created_date", "<", (clone $day)->addHours(-1 * $filter))->count();

                //Issue
                $info->total_issue_solved = Issue::where("chain", $chain->id)
                    ->where("open_date", "<", (clone $day)->addHours(-1 * $filter))->count();
                $total = Issue::where("chain", $chain->id)->groupBy("chain")
                    ->selectRaw("chain, COUNT(*) as count, SUM(total_minute) as total")->first();
                if ($total)
                    $total = $total->toArray();
                else
                    $total = ["total" => 0, "count" => 1];
                $issuePerform = $total["total"] / $total["count"] / 60 / 24;
                $info->issue_performance = $issuePerform;
                //Pull
                $pullCreator = unique_name(Pull::where("chain", $chain->id)->pluck("author")->toArray());
                $outbound = array_filter($pullCreator, function ($row) use ($contributors){
                    return !in_array($row, $contributors);
                });
                $outboundPulls = Pull::whereNotIn("author", $outbound)->where("chain", $chain->id)->count();
                $communityAttribute = $outboundPulls / (count($outbound) == 0 ? 1 : count($outbound));
                $info->community_attribute = $communityAttribute;
                $info->total_star = Repository::where("chain", $chain->id)->sum("total_star");
                $info->total_fork = Repository::where("chain", $chain->id)->sum("total_fork");
                $info->total_pull_merged = Pull::where([
                    ["chain", $chain->id],
                    ["status", "closed"],
                    ["created_date", "<", (clone $day)->addHours(-1 * $filter)]
                ])->count();
                $info->total_pull_merged = Pull::where([
                    ["chain", $chain->id],
                    ["created_date", "<", (clone $day)->addHours(-1 * $filter)]
                ])->count();
                $info->save();
            }
        }

//        //Issue
//        $total = Issue::groupBy("chain")->selectRaw("chain, COUNT(*) as count, SUM(total_minute) as total")->get()->toArray();
//        $issuePerform = array_sum(array_column($total, "total")) / array_sum(array_column($total, "count")) / 60 / 24;
//        setting()->set("issue_performance", number_format(floor($issuePerform), 2));
//        //Pull
//        $contributors = unique_name(Repository::pluck("total_contributor")->toArray());
//        $pullCreator = unique_name(Pull::pluck("author")->toArray());
//        $outbound = array_filter($pullCreator, function ($row) use ($contributors){
//            return !in_array($row, $contributors);
//        });
//        $outboundPulls = Pull::whereNotIn("author", $outbound)->count();
//        $communityAttribute = $outboundPulls / count($outbound);
//        setting()->set("community_attribute", number_format($communityAttribute, 2));
//        setting()->save();

    }
}
