<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainInfo;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\DeveloperStatistic;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SummarizeDeveloper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:developer {from}';

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

    public function handle(){
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
                "name" => "30_days",
                "value" => 24 * 30,
            ],
        ];
        $from = $this->argument("from") ?? 0;
        foreach (Contributor::where("id", ">=", $from)->orderBy("id", "ASC")->get() as $contributor){
            $queryCommit = Commit::where("author_list", "like", "%" . $contributor->login . "%");
            $queryIssue = Issue::where("creator", "like", "%" . $contributor->login . "%");
            $queryPull = Pull::where("author", "like", "%" . $contributor->login . "%");
            $queryPullMerged = Pull::where("author", "like", "%" . $contributor->login . "%")->where("status", "closed");
            foreach ($range as $item){

                $selectedDate = now()->addDays(-1 * $item["value"])->toDateString();
                if ($item["value"] == 0)
                    $selectedDate = "2020-01-01";
                $author = (clone $queryCommit)->where("exact_date", ">=", $selectedDate)->pluck("author_list")->toArray();
                $listContributor = array_filter(explode(",", implode(",", $author)));
                $values = array_count_values($listContributor);
                $totalCommit = isset($values[$contributor->login]) ? $values[$contributor->login] : 0;

                $totalIssue = (clone $queryIssue)->where("open_date", ">=", $selectedDate)->count();
                $totalPull = (clone $queryPull)->where("created_date", ">=", $selectedDate)->count();
                $totalPullMerged = (clone $queryPullMerged)->where("created_date", ">=", $selectedDate)->count();

                if (!$exist = DeveloperStatistic::where("range", $item["name"])->where("contributor_id", $contributor->id)->first())
                    $exist = DeveloperStatistic::create([
                        "range" => $item["name"],
                        "contributor_id" => $contributor->id
                    ]);

                $exist->total_commit = $totalCommit;
                $exist->total_issue = $totalIssue;
                $exist->total_pull_request = $totalPull;
                $exist->total_pull_merged = $totalPullMerged;
                if ($totalPull == 0)
                    $exist->merge_ratio = 0;
                else
                    $exist->merge_ratio = round($totalPullMerged / $totalPull * 100, 2);
                $exist->save();

                echo "Process contributor " . $contributor->login . " with range ". $item["name"] . PHP_EOL;
            }
        }

        echo "Done";
    }
}
