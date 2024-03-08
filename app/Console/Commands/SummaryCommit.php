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
        \Log::info("Begin summary commit at " . now("Asia/Bangkok")->toDateTimeString());
        ini_set("memory_limit", -1);
        $sha = CommitSHA::orderBy("id", "ASC")->get();
        foreach ($sha as $i => $item){
            $useKey = ((floor($i / 100) % 2 != 0) ? 2 : 1);
            $commit = Commit::whereId($item->commit_id)->first();
            $prefix = $commit->target_repo->github_prefix;
            $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $item->sha;
            $detail = (array) json_decode(get_github_data($detailUrl, 1, $useKey));
            if (isset($detail["message"])){
                Log::error($detail["message"]);
                continue;
            }
            try{
                $commit->additions += $detail["stats"]->additions;
                $commit->deletions += $detail["stats"]->deletions;
                $commit->save();

                $item->delete();
            }
            catch (\Exception $exception){
                \Log::info(json_encode($detail));
                return;
            }
        }

        \Log::info("End summary commit at " . now("Asia/Bangkok")->toDateTimeString());
    }
}
