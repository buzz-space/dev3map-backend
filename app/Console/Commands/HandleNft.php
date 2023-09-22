<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\CommitSHA;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HandleNft extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staking:campaign';

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

        // new staker
//        $this->info("Start stake!");
//        $stakeName = $this->ask("Stake name");
//        $currentTime = $this->ask("Start");
//        $duration = $this->ask("Duration");
////
//
//        $this->handleData($currentTime);
//
////
//        DB::table("nft_list")->insert([
//            "name" => $stakeName,
//            "time" => $currentTime,
//            "start_time" => $currentTime,
//            "end_stake" => $currentTime + $duration,
//            "term" => $duration,
////            "is_end_stake" => false
//        ]);

//        ini_set("memory_limit", -1);
//        $oldCommit = DB::table("commits_backup")->where("id", "<=", setting("last_commit"))->get()->toArray();
//        $newCommit = Commit::where("id", "<=", setting("last_commit"))->where("id", ">=", 12570)->get();
//        foreach ($newCommit as $commit){
//            echo "Commit ID: " . $commit->id . PHP_EOL;
//            $index = array_search($commit->id, array_column($oldCommit, "id"));
//            if ($index !== false){
//                $commit->additions = $oldCommit[$index]->additions;
//                $commit->deletions = $oldCommit[$index]->deletions;
//                $commit->save();
//            }
//        }


    }

    public function handle2()
    {
        // query
        $timeFind = $this->ask("Time to find?");
        $nameFind = $this->ask("Name to find?");

        $record = DB::table("nft_list")->where("name", $nameFind)->first();

        if ($record) {
            if ($record->is_end_stake) {
                echo print_r($record, true) . PHP_EOL;
                return;
            }

            $this->handleData($timeFind);

            $record = DB::table("nft_list")->where("name", $nameFind)->first();
            // claim reward
            $record->pending_reward = 0;
            echo print_r($record, true) . PHP_EOL;
        }

        echo "Not found!";
    }

    private function handleData($currentTime)
    {
        $reward_per_second = 1;

        $terms = [
            [
                "term" => 15,
                "percent" => 40,
            ],
            [
                "term" => 30,
                "percent" => 60,
            ],
        ];
        foreach ($terms as $term) {
            // Danh sách những nft theo từng term (không bao gồm đã end_stake)
            $nftList = DB::table("nft_list")
                ->where("term", $term)
                ->where("is_end_stake", false)
                ->orderBy("end_stake", "ASC")
                ->pluck("id");

            $timeCalc = null;
            $stakerCount = count($nftList);
            $reward = 0;
            foreach ($nftList as $id) {
                $item = DB::table("nft_list")->where("id", $id)->first();
                if ($timeCalc)
                    $item->time = $timeCalc;
                if ($item->end_stake <= $currentTime) {
                    // xử lý ông này đã hết thời hạn stake
                    // cộng cái reward kia vào reward cộng dồn
                    $reward += ($item->end_stake - $item->time) * $reward_per_second * $term["percent"] / 100 / $stakerCount;
                    // tính pending reward
                    $item->pending_reward += $reward;
                    // trừ đi 1 ông staker
                    $stakerCount -= 1;
                    // gắn cái time vào
                    $timeCalc = $item->end_stake;
                    // set is_end_stake
                    $item->is_end_stake = true;
                } else {
                    // gắn time mới vào để tính toán (nếu có)
                    // xử lý pending reward
                    $accumulateReward = ($currentTime - $item->time) * $reward_per_second * $term["percent"] / 100 / $stakerCount + $reward;
                    $item->pending_reward += $accumulateReward;
                    // set thời gian mới nhất
                    $item->time = $currentTime;
                }
                DB::table("nft_list")->where("id", $id)->update((array)$item);
            }
        }
    }
}
