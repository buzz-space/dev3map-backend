<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
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
    public function handle()
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
                    $thisMonth = (clone $dateFirstCommit)->addMonths($i);
                    for ($j = 1; $j <= 2; $j++) {
                        $startWeek = (clone $thisMonth)->addDays(14 * ($j - 1))->startOfDay();
                        if ($j == 1)
                            $startWeek = (clone $thisMonth)->startOfMonth();
                        $endWeek = (clone $thisMonth)->addDays(14 * $j)->endOfDay();
                        if ($j == 2)
                            $endWeek = (clone $thisMonth)->endOfMonth();

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
}
