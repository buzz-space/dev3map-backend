<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetCommitChart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commit:chart';

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
    public function handles()
    {
        foreach (Chain::all() as $chain) {
            echo "Chain " . $chain->name . PHP_EOL;

            if ($chain->total_commit <= 0)
                continue;
            // Get commit chart
            $firstCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "ASC")->first();
            $lastCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "DESC")->first();
            if ($firstCommit && $lastCommit) {
                $dateFirstCommit = Carbon::createFromTimestamp(strtotime($firstCommit->exact_date));
                $dateLastCommit = Carbon::createFromTimestamp(strtotime($lastCommit->exact_date));
                echo "From " . $dateFirstCommit->toDateTimeString() . " to " . $dateLastCommit->toDateTimeString() . PHP_EOL;
                $diff = $dateFirstCommit->diffInMonths($dateLastCommit) + ($dateFirstCommit->day > $dateLastCommit->day ? 2 : 1);
                for ($i = 0; $i < $diff; $i++) {
                    $thisMonth = (clone $dateFirstCommit)->addMonths($i)->startOfMonth();
                    for ($j = 1; $j <= 2; $j++) {
                        if ($j == 1){
                            $startWeek = (clone $thisMonth)->startOfMonth();
                            $endWeek = (clone $thisMonth)->addDays(14)->endOfDay();
                        }
                        else{
                            $startWeek = (clone $thisMonth)->addDays(15)->startOfDay();
                            $endWeek = (clone $thisMonth)->endOfMonth();
                        }

                        echo "Week $j, start: " . $startWeek->toDateTimeString() . ", end: " . $endWeek->toDateTimeString() . PHP_EOL;

                        $total_commit = Commit::where("chain", $chain->id)
                            ->where("exact_date", ">=", $startWeek->toDateTimeString())
                            ->where("exact_date", "<", $endWeek->toDateTimeString())
                            ->sum("total_commit");

                        $total_additions = Commit::where("chain", $chain->id)
                            ->where("exact_date", ">=", $startWeek->toDateTimeString())
                            ->where("exact_date", "<", $endWeek->toDateTimeString())
                            ->sum("additions");

                        $total_deletions = Commit::where("chain", $chain->id)
                            ->where("exact_date", ">=", $startWeek->toDateTimeString())
                            ->where("exact_date", "<", $endWeek->toDateTimeString())
                            ->sum("deletions");

                        CommitChart::create([
                            "chain" => $chain->id,
                            "week" => $j,
                            "month" => $thisMonth->month,
                            "year" => $thisMonth->year,
                            "total_commit" => $total_commit,
                            "total_additions" => $total_additions,
                            "total_deletions" => $total_deletions,
                            "total_fork_commit" => 0,
                            'from' => $startWeek->toDateString(),
                            "to" => $endWeek->toDateString()
                        ]);
                    }
                }
            }

//            $choice = $this->choice("Continue?", ["no", "yes"]);
//            if ($choice == "no")
//                break;
        }
        echo "Done";
    }

    public function handle()
    {
        $chainId = $this->ask("Chain id?");
        $chain = Chain::whereId($chainId)->first();
        echo "Chain name: " . $chain->name . PHP_EOL;
        $total = 0;
        foreach (Repository::where("chain", $chain->id)->get() as $repo){
            echo "Repo name: " . $repo->name . PHP_EOL;
            $prefix = $repo->github_prefix;
            $url = "https://api.github.com/repos/$prefix/commits?per_page=100";
//            echo "url " . $url . PHP_EOL;
            $lastPage = get_last_page(get_github_data($url, "header"));
            $totalCommitLastPage = count(json_decode(get_github_data($url . "&page=$lastPage")));
            $totalCommit = ($lastPage - 1) * 100 + $totalCommitLastPage;
            echo "Has " . $totalCommit . PHP_EOL;
            $total += $totalCommit;
        }
        echo "So total commit of chain " . $chain->name . " is " . number_format($total);
//        $chain->total_commit = $total;
//        $chain->save();
    }
}
