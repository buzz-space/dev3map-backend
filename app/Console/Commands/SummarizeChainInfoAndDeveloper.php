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
    protected $signature = 'summarize:developer';

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
            $developers = Developer::where("chain", $chain->id)->pluck("author")->toArray();
            $data = $this->processDeveloperRepository(implode(",", $developers));
            $chain->total_full_time_developer += $data["full_time"];
            $chain->total_part_time_developer += $data["part_time"];
            $chain->total_one_time_developer += $data["one_time"];
            $chain->total_developer += ($data["full_time"] + $data["part_time"] + $data["one_time"]);
            $chain->save();

        }

        echo "Done";
    }

    private function processDeveloperRepository($developerString)
    {
        $developers = [];
        foreach (explode(",", $developerString) as $developer){
            if (isset($developers[$developer]))
                $developers[$developer] += 1;
            else
                $developers[$developer] = 1;
        }

        return [
            'full_time' => count(array_filter($developers, function ($row){
                return $row > 10;
            })),
            'part_time' => count(array_filter($developers, function ($row){
                return $row > 1 && $row <= 10;
            })),
            'one_time' => count(array_filter($developers, function ($row){
                return $row == 1;
            })),
        ];
    }
}
