<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
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
        $chain = Chain::find($this->ask("Chain id?"));
        foreach (Repository::where("chain", $chain->id)->orderBy("id", "ASC")->get() as $repo) {
//            if ($repo->id < 33) continue;
            echo "Repo: " . $repo->name . PHP_EOL;
            $prefix = $repo->github_prefix;
            $contributors = ($c = Contributor::where("repo", $repo->id)->first()) ? explode(",", $c->contributors) : [];
            $commits = Commit::where("repo", $repo->id)->get();
            foreach ($commits as $commit) {
//                if ($commit->id < 13655) continue;
                echo "Commit ID: " . $commit->id . PHP_EOL;
                $sha = CommitSHA::where("commit_id", $commit->id)->pluck("sha");
                $total_dev = [];
                $total_commit = 0;
                $total_addition = 0;
                $total_deletion = 0;
                $deleteSHA = [];
                foreach ($sha as $item) {
                    $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $item;
                    $detail = json_decode(get_github_data($detailUrl, "body"));
                    if (isset($detail->message))
                        throw new \Exception($commit->id . " with SHA $item: " . $detail->message);
                    if (isset($detail->author)) {
                        $author = $detail->author->login;
//                        $note = "author";
                    }
                    else {
                        $author = $detail->commit->author->name;
//                        $note = "commit author";
                    }
                    if (in_array($author, $contributors)){
                        $total_dev[] = $author;
                        $total_commit += 1;
                        $total_addition += $detail->stats->additions;
                        $total_deletion += $detail->stats->deletions;
//                        Log::info($commit->id . ": " . $note . "-" . $author);
                    }
                    else
                        $deleteSHA[] = $item;
                }
                CommitSHA::where("commit_id", $commit->id)->whereIn("sha", $deleteSHA)->delete();
                if (empty($total_dev)){
                    $commit->delete();
                    continue;
                }
                $commit->author_list = implode(",", $total_dev);
                $commit->total_commit = $total_commit;
                $commit->additions = $total_addition;
                $commit->deletions = $total_deletion;
                $commit->save();
            }
        }
    }
}
