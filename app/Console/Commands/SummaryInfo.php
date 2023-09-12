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
        $day = Carbon::createFromTimestamp(strtotime("2023-09-01"));
        foreach (Chain::orderBy("id", "ASC")->get() as $chain){
            echo "Chain name: " . $chain->name . PHP_EOL;
            $range = [
                [
                    "name" => "all",
                    "value" => 0,
                    "skip" => false
                ],
                [
                    "name" => "24_hours",
                    "value" => 24,
                    "skip" => false
                ],
                [
                    "name" => "7_days",
                    "value" => 24 * 7,
                    "skip" => false
                ],
                [
                    "name" => "before_7_days",
                    "value" => 24 * 7,
                    "skip" => true
                ],
                [
                    "name" => "30_days",
                    "value" => 24 * 30,
                    "skip" => false
                ],
                [
                    "name" => "before_30_days",
                    "value" => 24 * 30,
                    "skip" => true
                ],

            ];

            foreach ($range as $item){
                $filter = $item["value"];$range_name = $item["name"];$skip = $item["skip"];
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
                $contributors = unique_name(Repository::where("chain", $chain->id)->pluck("total_contributor")->toArray());
                if ($filter == 0){
                    $commits = Commit::where("chain", $chain->id)->get()->toArray();
                    $developers = Commit::where("chain", $chain->id)->where("exact_date", ">=", (clone $day)->addMonths(-3))->get()->toArray();
                    $repositories = Repository::where("chain", $chain->id)->count();
                    $issues = Issue::where("chain", $chain->id)->count();
                    $pulls = Pull::where("chain", $chain->id)->count();
                    $mergedPulls = Pull::where("chain", $chain->id)->where("status", "closed")->count();
                    $info->total_star = Repository::where("chain", $chain->id)->sum("total_star");
                    $info->total_fork = Repository::where("chain", $chain->id)->sum("total_fork");
                    // Issue performance
                    $total = Issue::where("chain", $chain->id)->groupBy("chain")
                        ->selectRaw("chain, COUNT(*) as count, SUM(total_minute) as total")->first();
                    if ($total)
                        $total = $total->toArray();
                    else
                        $total = ["total" => 0, "count" => 1];
                    $issuePerform = $total["total"] / $total["count"] / 60 / 24;
                    $info->issue_performance = $issuePerform;
                    // Community Attribute
                    $pullCreator = unique_name(Pull::where("chain", $chain->id)->pluck("author")->toArray());
                    $outbound = array_filter($pullCreator, function ($row) use ($contributors){
                        return !in_array($row, $contributors);
                    });
                    $outboundPulls = Pull::whereNotIn("author", $outbound)->where("chain", $chain->id)->count();
                    $communityAttribute = $outboundPulls / (count($outbound) == 0 ? 1 : count($outbound));
                    $info->community_attribute = $communityAttribute;
                }
                else{
                    $commits = Commit::where("chain", $chain->id);
                    $developers = Commit::where("chain", $chain->id);
                    $repositories = Repository::where("chain", $chain->id);
                    $issues = Issue::where("chain", $chain->id);
                    $pulls = Pull::where("chain", $chain->id);
                    $mergedPulls = Pull::where("chain", $chain->id)->where("status", "closed");
                    if ($skip){
                        $commits->where([
                            ["exact_date", ">=", (clone $day)->addHours(-2 * $filter)],
                            ["exact_date", "<", (clone $day)->addHours(-1 * $filter)],
                        ]);
                        $developers->where([
                            ["exact_date", ">=", (clone $day)->addHours(-2 * $filter)],
                            ["exact_date", "<", (clone $day)->addHours(-1 * $filter)],
                        ]);
                        $repositories->where([
                            ["created_date", ">=", (clone $day)->addHours(-2 * $filter)],
                            ["created_date", "<", (clone $day)->addHours(-1 * $filter)],
                        ]);
                        $issues->where([
                            ["open_date", ">=", (clone $day)->addHours(-2 * $filter)],
                            ["open_date", "<", (clone $day)->addHours(-1 * $filter)],
                        ]);
                        $pulls->where([
                            ["created_date", ">=", (clone $day)->addHours(-2 * $filter)],
                            ["created_date", "<", (clone $day)->addHours(-1 * $filter)],
                        ]);
                        $mergedPulls->where([
                            ["created_date", ">=", (clone $day)->addHours(-2 * $filter)],
                            ["created_date", "<", (clone $day)->addHours(-1 * $filter)],
                        ]);
                    } else {
                        $commits->where("exact_date", ">=", (clone $day)->addHours(-1 * $filter));
                        $developers->where("exact_date", ">=", (clone $day)->addHours(-1 * $filter));
                        $repositories->where("created_date", ">=", (clone $day)->addHours(-1 * $filter));
                        $issues->where("open_date", ">=", (clone $day)->addHours(-1 * $filter));
                        $pulls->where("created_date", ">=", (clone $day)->addHours(-1 * $filter));
                        $mergedPulls->where("created_date", ">=", (clone $day)->addHours(-1 * $filter));
                    }
                    $commits = $commits->get()->toArray();
                    $developers = $developers->get()->toArray();
                    $repositories = $repositories->count();
                    $issues = $issues->count();
                    $pulls = $pulls->count();
                    $mergedPulls = $mergedPulls->count();
                }
                // Commits
                $info->total_commits = array_sum(array_column($commits, "total_commit"));
                // Developer (3 month range)
                $fullTime = unique_name(array_column($developers, "full_time"));
                $fullTime = array_filter($fullTime, function ($c) use ($contributors){
                    return !empty($c) && in_array($c, $contributors);
                });
                $partTime = unique_name(array_column($developers, "part_time"));
                $partTime = array_filter($partTime, function ($c) use ($contributors, $fullTime){
                    return !empty($c) && in_array($c, $contributors) && !in_array($c, $fullTime);
                });
                $info->full_time = implode(",", $fullTime);
                $info->part_time = implode(",", $partTime);
                $info->full_time_developer = count($fullTime);
                $info->part_time_developer = count($partTime);
                // Repositories
                $info->total_repository = $repositories;
                // Issues
                $info->total_issue_solved = $issues;
                // Pulls
                $info->total_pull_merged = $mergedPulls;
                $info->total_pull_request = $pulls;
                $info->save();
            }
        }

        //Issue
        $total = Issue::groupBy("chain")->selectRaw("chain, COUNT(*) as count, SUM(total_minute) as total")->get()->toArray();
        $issuePerform = array_sum(array_column($total, "total")) / array_sum(array_column($total, "count")) / 60 / 24;
        setting()->set("issue_performance", number_format(floor($issuePerform), 2));
        //Pull
        $contributors = unique_name(Repository::pluck("total_contributor")->toArray());
        $pullCreator = unique_name(Pull::pluck("author")->toArray());
        $outbound = array_filter($pullCreator, function ($row) use ($contributors){
            return !in_array($row, $contributors);
        });
        $outboundPulls = Pull::whereNotIn("author", $outbound)->count();
        $communityAttribute = $outboundPulls / count($outbound);
        setting()->set("community_attribute", number_format($communityAttribute, 2));
        setting()->save();

    }
}
