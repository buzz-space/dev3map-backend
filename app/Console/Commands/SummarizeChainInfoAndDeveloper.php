<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Developer;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SummarizeChainInfoAndDeveloper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summarize:chain';

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
        foreach (Chain::all() as $chain) {
            echo "Chain " . $chain->name . PHP_EOL;
//            if ($chain->id < 27) continue;
            // Summarize contributor
            $chainContributor = $chain->repositories()->pluck("total_contributor");
            $contributors = [];
            foreach ($chainContributor as $c) {
                $contributors = array_merge($contributors, explode(",", $c));
            }
            $chain->total_contributor = count(array_unique($contributors));

            // Summarize Commit
//            $chain->total_commit = Commit::where("chain", $chain->id)->sum("total_commit");
//            if ($chain->total_commit == 0)
//                continue;

            // Summarize developer
            $firstCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "ASC")->first();
            $lastCommit = Commit::where("chain", $chain->id)->orderBy("exact_date", "DESC")->first();
            $dateFirstCommit = Carbon::createFromTimestamp(strtotime($firstCommit->exact_date));
            $dateLastCommit = Carbon::createFromTimestamp(strtotime($lastCommit->exact_date));
            echo "From " . $dateFirstCommit->toDateTimeString() . " to " . $dateLastCommit->toDateTimeString() . PHP_EOL;
            $diff = $dateFirstCommit->diffInMonths($dateLastCommit) + ($dateFirstCommit->day > $dateLastCommit->day ? 2 : 1);
            for ($i = 0; $i < $diff; $i++) {
                $exactMonth = (clone $dateFirstCommit)->addMonths($i);
                echo "Month " . $exactMonth->month . ", year: " . $exactMonth->year . PHP_EOL;
                $authors = Commit::where("chain", $chain->id)
                    ->where("exact_date", ">=", $exactMonth->firstOfMonth()->toDateTimeString())
                    ->where("exact_date", "<", $exactMonth->endOfMonth()->toDateTimeString())
                    ->pluck("author_list")->toArray();
                $devs = [];
                foreach ($authors as $author) {
                    $lst = array_count_values(explode(",", $author));
                    foreach ($lst as $key => $item) {
                        if (isset($devs[$key]))
                            $devs[$key] += $item;
                        else
                            $devs[$key] = $item;
                    }
                }

//                write_to_file("devs.txt", print_r($devs, true)); return;

                if (!$d = Developer::where("chain", $chain->id)
                    ->where("month", $exactMonth->month)
                    ->where("year", $exactMonth->year)->first()
                ) {
                    $d = new Developer();
                    $d->chain = $chain->id;
                    $d->month = $exactMonth->month;
                    $d->year = $exactMonth->year;
                }
                $d->author = implode(',', $authors);
                $d->total_developer = count($devs);
                $d->total_commit = 0;
                foreach ($devs as $dev => $commit_count) {
//                    echo "Dev " . $dev . " with " . $commit_count . " commits" . PHP_EOL;
                    if ($commit_count == 1)
                        $d->total_one_time += 1;
                    if ($commit_count > 1 && $commit_count <= 10)
                        $d->total_part_time += 1;
                    if ($commit_count > 10)
                        $d->total_full_time += 1;
                    $d->total_commit += $commit_count;
                }
                $d->save();
//            }

                $chain->save();

//            $choice = $this->choice("Continue?", ["no", "yes"]);
//            if ($choice == "no")
//                break;
            }

            echo "Done";
        }
    }
}
