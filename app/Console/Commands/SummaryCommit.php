<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\CommitSHA;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SummaryCommit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:commit';

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
        ini_set("memory_limit", -1);
        $commits = Commit::orderBy("id", "ASC")->get();
        $lastCommit = setting("last_commit", 0);
        $counting = 0;
        foreach ($commits as $commit) {
            $repo = Repository::find($commit->repo);
            $prefix = $repo->github_prefix;
            if ($commit->id < $lastCommit) continue;
            echo "Commit ID: " . $commit->id . PHP_EOL;
            $sha = CommitSHA::where("commit_id", $commit->id)->pluck("sha");
            $total_addition = 0;
            $total_deletion = 0;
            foreach ($sha as $item) {
                $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $item;
                $detail = json_decode(get_github_data($detailUrl, "body", 2));
                if (isset($detail->message))
                    throw new \Exception($commit->id . " with SHA $item: " . $detail->message);
                $total_addition += $detail->stats->additions;
                $total_deletion += $detail->stats->deletions;
            }
            $commit->additions = $total_addition;
            $commit->deletions = $total_deletion;
            $commit->save();

//            $chart = CommitChart::where("chain", $repo->chain)
//                ->where("from", "<=", $commit->exact_date)
//                ->where("to", ">=", $commit->exact_date)
//                ->first();
//            echo "Chart ID: " . $chart->id . PHP_EOL;
//            $chart->total_additions += $total_addition;
//            $chart->total_deletions += $total_deletion;
//            $chart->save();

            setting()->set("last_commit", $commit->id);
            setting()->save();

            $counting++;
            if ($counting == 5000)
                throw new \Exception("Limit reached!");

//            return;
        }
    }
}
