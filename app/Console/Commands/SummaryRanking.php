<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainInfo;
use Illuminate\Console\Command;

class SummaryRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:ranking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Summarize chain and developer info';

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
        \Log::info("Begin summary ranking at " . now("Asia/Bangkok")->toDateTimeString());
        $range = [
            [
                "name" => "all",
                "value" => 0,
            ],
            [
                "name" => "7_days",
                "value" => 24 * 7,
            ],
            [
                "name" => "before_7_days",
                "value" => 24 * 14,
            ],
            [
                "name" => "before_30_days",
                "value" => 24 * 60,
            ],
            [
                "name" => "21_days",
                "value" => 24 * 21,
            ],
            [
                "name" => "30_days",
                "value" => 24 * 30,
            ],
        ];

        $starAll = [];
        $forkAll = [];
        foreach ($range as $r) {
            $info = ChainInfo::where("range", $r["name"])->orderBy("chain", "ASC")->selectRaw(
                "chain, total_commits, total_issue_solved, total_pull_merged, total_pull_request, total_star, total_fork, (full_time_developer + part_time_developer) as total_developer"
            )->get()->toArray();
            $chainKeys = array_column($info, "chain");
            $sortByCommit = array_combine($chainKeys, array_column($info, "total_commits"));
            $sortByIssue = array_combine($chainKeys, array_column($info, "total_issue_solved"));
            $sortByPRSolved = array_combine($chainKeys, array_column($info, "total_pull_merged"));
            $sortByPR = array_combine($chainKeys, array_column($info, "total_pull_request"));
            $sortByDeveloper = array_combine($chainKeys, array_column($info, "total_developer"));
            $sortByStar = array_combine($chainKeys, array_column($info, "total_star"));
            $sortByFork = array_combine($chainKeys, array_column($info, "total_fork"));
            if ($r["name"] == "all") {
                $starAll = $sortByStar;
                $forkAll = $sortByFork;
            }
            else{
                foreach ($chainKeys as $i) {
                    $sortByStar[$i] = $starAll[$i] - $sortByStar[$i];
                    $sortByFork[$i] = $forkAll[$i] - $sortByFork[$i];
                }
            }

            arsort($sortByCommit);
            arsort($sortByIssue);
            arsort($sortByPRSolved);
            arsort($sortByDeveloper);
            arsort($sortByFork);
            arsort($sortByStar);
            arsort($sortByPR);


            $sortByCommit = array_keys($sortByCommit);
            $sortByIssue = array_keys($sortByIssue);
            $sortByPRSolved = array_keys($sortByPRSolved);
            $sortByDeveloper = array_keys($sortByDeveloper);
            $sortByFork = array_keys($sortByFork);
            $sortByStar = array_keys($sortByStar);
            $sortByPR = array_keys($sortByPR);

            foreach (Chain::orderBy("id", "ASC")->get() as $i => $chain) {
                echo "Chain " . $chain->name . PHP_EOL;
                if ($chain->is_repo) {
                    $chain->github_prefix = str_replace("/", "-", $chain->github_prefix);
                    $chain->save();
                }
                $chainInfo = ChainInfo::where("chain", $chain->id)->where("range", $r["name"])->first();

                $commit_index = array_search($chain->id, $sortByCommit);
                $pull_index = array_search($chain->id, $sortByPRSolved);
                $issue_index = array_search($chain->id, $sortByIssue);
                $dev_index = array_search($chain->id, $sortByDeveloper);
                $star_index = array_search($chain->id, $sortByStar);
                $fork_index = array_search($chain->id, $sortByFork);
                $pr_index = array_search($chain->id, $sortByPR);
                // Rank
                $chainInfo->commit_rank = $commit_index !== false ? 1 + ($commit_index > 100 ? 100 : $commit_index) : 101;
                $chainInfo->pull_rank = $pull_index !== false ? 1 + ($pull_index > 100 ? 100 : $pull_index) : 101;
                $chainInfo->issue_rank = $issue_index !== false ? 1 + ($issue_index > 100 ? 100 : $issue_index) : 101;
                $chainInfo->dev_rank = $dev_index !== false ? 1 + ($dev_index > 100 ? 100 : $dev_index) : 101;
                $chainInfo->star_rank = $star_index !== false ? 1 + ($star_index > 100 ? 100 : $star_index) : 101;
                $chainInfo->fork_rank = $fork_index !== false ? 1 + ($fork_index > 100 ? 100 : $fork_index) : 101;
                $chainInfo->pr_rank = $pr_index !== false ? 1 + ($pr_index > 100 ? 100 : $pr_index) : 101;
                // Score
                $commit_score = 101 - ($chain->commit_rank > 101 ? 101 : $chainInfo->commit_rank);
                $pull_score = 101 - ($chain->pull_rank > 101 ? 101 : $chainInfo->pull_rank);
                $issue_score = 101 - ($chain->issue_rank > 101 ? 101 : $chainInfo->issue_rank);
                $dev_score = 101 - ($chain->dev_rank > 101 ? 101 : $chainInfo->dev_rank);
//                $star_score = 101 - ($chain->star_rank > 101 ? 101 : $chain->star_rank);
                $fork_score = 101 - ($chain->fork_rank > 101 ? 101 : $chainInfo->fork_rank);
                $pr_score = 101 - ($chain->pr_rank > 101 ? 101 : $chainInfo->pr_rank);

                $chainInfo->seriousness = ($commit_score + $issue_score + $pull_score + $dev_score) / 4;
                $chainInfo->rising_star = ($fork_score + $commit_score + $pr_score) / 3;
                $chainInfo->ibc_astronaut = ($commit_score + $issue_score + $pull_score) / 3;
                $chainInfo->save();
            }

        }
        \Log::info("End summary ranking at " . now("Asia/Bangkok")->toDateTimeString());

    }


}
