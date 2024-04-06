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
    protected $signature = 'commit:chart {from_date}';

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
        $date = $this->ask("From?", "2020-01-01");
        foreach (Chain::orderBy("id", "ASC")->get() as $chain) {
            echo "Chain " . $chain->name . PHP_EOL;

//            if ($chain->total_commit <= 0)
//                continue;
            // Get commit chart
            $firstCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "ASC")->first();
            $lastCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "DESC")->first();
            if ($firstCommit && $lastCommit) {
                $dateFirstCommit = Carbon::createFromTimestamp(strtotime($date));
                $dateLastCommit = Carbon::createFromTimestamp(strtotime($lastCommit->exact_date));
                if ($dateFirstCommit->gt($dateLastCommit)) continue;
                echo "From " . $dateFirstCommit->toDateTimeString() . " to " . $dateLastCommit->toDateTimeString() . PHP_EOL;
                $diff = $dateFirstCommit->diffInMonths($dateLastCommit) + ($dateFirstCommit->day > $dateLastCommit->day ? 2 : 1);
                for ($i = 0; $i < $diff; $i++) {
                    $thisMonth = (clone $dateFirstCommit)->addMonths($i)->startOfMonth();
                    for ($j = 1; $j <= 2; $j++) {
                        $startWeek = ($j == 1) ? (clone $thisMonth)->startOfMonth() : (clone $thisMonth)->addDays(15)->startOfDay();
                        $endWeek = ($j == 1) ? (clone $thisMonth)->addDays(14)->endOfDay() : (clone $thisMonth)->endOfMonth();

                        echo "Week $j, start: " . $startWeek->toDateTimeString() . ", end: " . $endWeek->toDateTimeString() . PHP_EOL;

                        $data = Commit::where("chain", $chain->id)
                            ->where("exact_date", ">=", $startWeek->toDateTimeString())
                            ->where("exact_date", "<", $endWeek->toDateTimeString())
                            ->select("total_commit", "additions", "deletions")->get()->toArray();

                        $total_commit = array_sum(array_column($data, "total_commit"));
                        $total_additions = array_sum(array_column($data, "additions"));
                        $total_deletions = array_sum(array_column($data, "deletions"));

                        if (!$exist = CommitChart::where([
                            ["chain", $chain->id],
                            ["week", $j],
                            ["month", $thisMonth->month],
                            ["year", $thisMonth->year]
                        ])->first())
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
                        else {
                            $exist->total_commit = $total_commit;
                            $exist->total_additions = $total_additions;
                            $exist->total_deletions = $total_deletions;
                            $exist->save();
                        }
                    }
                }
            }

//            $choice = $this->choice("Continue?", ["no", "yes"]);
//            if ($choice == "no")
//                break;
        }
        send_telegram_message("Get commit chart " . now("Asia/Bangkok")->toDateTimeString());
    }
}
