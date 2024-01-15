<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\CommitSHA;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SummaryCommit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:commit {from_date)';

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
        $from_date = $this->argument("from_date");
        $key = 1;
        $commits = Commit::where("exact_date", ">=", $from_date)->orderBy("id", "ASC")->get();
        foreach ($commits as $commit) {
            try{
                $repo = Repository::find($commit->repo);
                $prefix = $repo->github_prefix;
                $sha = CommitSHA::where("commit_id", $commit->id)->pluck("sha");
                $total_addition = 0;
                $total_deletion = 0;
                foreach ($sha as $item) {
                    $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $item;
                    $detail = (array) json_decode(get_github_data($detailUrl, "body"), $key);
                    if (isset($detail->message)){
                        Log::error($detail->message);
                        if (strpos($detail->message, "API rate limit") !== false) {
                            $key = ($key == 1) ? 2 : 1;
                            continue;
                        }
                    }
                    $total_addition += $detail["stats"]["additions"];
                    $total_deletion += $detail["stats"]["deletions"];
                }
                $commit->additions = $total_addition;
                $commit->deletions = $total_deletion;
                $commit->save();

                setting()->set("last_commit", $commit->id);
                setting()->save();

            } catch (\Exception $exception){
                Log::error($exception->getMessage());
                $key = ($key == 1) ? 2 : 1;
            }


//            $chart = CommitChart::where("chain", $repo->chain)
//                ->where("from", "<=", $commit->exact_date)
//                ->where("to", ">=", $commit->exact_date)
//                ->first();
//            if (!$chart){
//                $date = Carbon::createFromTimestamp(strtotime($commit->exact_date));
//                $chart = new CommitChart();
//                $chart->from =  Carbon::create($date->year, $date->month, $date->day > 15 ? 16 : 1);
//                $chart->to = Carbon::create($date->year, $date->month, $date->day > 15 ? 15 : $date->daysInMonth);
//                $chart->week = $date->day > 15 ? 2 : 1;
//                $chart->month = $date->month;
//                $chart->year = $date->year;
//                $chart->chain = $repo->chain;
//                $chart->save();
//            }
//            $chart->total_additions += $total_addition;
//            $chart->total_deletions += $total_deletion;
//            $chart->save();
        }

        return 1;
    }

    public function handles(){
        foreach (Repository::all() as $item){
            $item->total_commit = $item->commits()->sum("total_commit");
            $item->save();

            echo "Processed repo " . $item->name . PHP_EOL;
        }
    }
}
