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
        ini_set("memory_limit", -1);
        $key = 1;
        $sha = CommitSHA::orderBy("id", "ASC")->get();
        foreach ($sha as $item){
            setting()->set("sha_id", $item->id);
            setting()->save();
            $commit = Commit::whereId($item->commit_id)->first();
            $prefix = $commit->target_repo->github_prefix;
            $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $item->sha;
            $detail = (array) json_decode(get_github_data($detailUrl, "body"), $key);
            if (isset($detail["message"])){
                Log::error($detail["message"]);
                if (strpos($detail["message"], "API rate limit") !== false) {
                    $key = ($key == 1) ? 2 : 1;
                    continue;
                }
                if (strpos($detail["message"], "Not found") !== false) {
                    $item->delete();
                    continue;
                }
                return 1;
            }

            $commit->additions += $detail["stats"]["additions"];
            $commit->deletions += $detail["stats"]["deletions"];
            $commit->save();

            $item->delete();
        }

        return 1;
    }
}
