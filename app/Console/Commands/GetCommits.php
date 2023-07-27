<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitSHA;
use Botble\Statistic\Models\Repository;

//use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetCommits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:commits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Commits';

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
        $totalRequest = 0;
        $lastExactDate = null;
        $begin = "2018-01-01";
        $lastRepo = setting("last_repo", 0);
        $start = now();
        foreach (Chain::orderBy("id", "ASC")->get() as $chain) {
            $repositories = Repository::where("chain", $chain->id)->orderBy("id", "ASC")->get();
            echo "Chain: " . $chain->name . " with " . count($repositories) . " repositories!" . PHP_EOL;
            if ($chain->id < 29) continue;
            try {
                foreach ($repositories as $j => $repository) {
                    if ($repository->chain == 29 && $j < 18) continue;
//            if (!in_array($repository->chain, [27, 43, 60])) continue;
//                if ($repository->id < $lastRepo) continue;
                    echo ($j + 1) . ": " . $repository->name . PHP_EOL;
                    $prefix = $repository->github_prefix;
                    // Get commit
//                if ($lastCommit = Commit::where("repo", $repository->id)->orderBy("exact_date", "ASC")->first())
//                    $last = $lastCommit->exact_date;
                    $url = "https://api.github.com/repos/$prefix/commits?per_page=100";
                    $url .= "&since=" . date(DATE_ISO8601, strtotime($begin));
                    $lastPage = get_last_page(get_github_data($url, "header"));
                    $totalRequest += 1;
                    echo "Total page: " . $lastPage . PHP_EOL;
                    for ($i = 1; $i <= $lastPage; $i++) {
//                        if ($repository->chain == 19 && $repository->id == 785 && $i < 104) continue;
                        echo "Process page $i..." . PHP_EOL;
                        $commitUrl = $url . "&page=$i";
                        $data = json_decode(get_github_data($commitUrl));
                        $totalRequest += 1;
                        if (isset($data->message)) {
                            Log::info("Repository " . $repository->name . ": " . $data->message);
                            continue;
//                        throw new \Exception("Limit reached!");
                        }
//                        echo $commitUrl . PHP_EOL;
                        $date = null;
                        $save = null;
                        $sha = [];
//                    Log::info(print_r($data, true));
                        foreach ($data as $commit) {
//                        $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $commit->sha;
//                        $detail = json_decode(get_github_data($detailUrl));
                            $totalRequest += 1;
                            $commitDate = date("Y-m-d", strtotime($commit->commit->author->date));
                            if ($date != $commitDate) {
                                if ($save) {
                                    $save->author_list = implode(",", $save->author_list);
                                    $save->save();

                                    // save sha
                                    $exists = CommitSHA::where("commit_id", $save->id)->pluck("sha")->toArray();
                                    $sha = array_filter($sha, function ($row) use ($exists){
                                       return !in_array($row, $exists);
                                    });
                                    foreach ($sha as $z) {
                                        CommitSHA::create([
                                            "sha" => $z,
                                            "commit_id" => $save->id
                                        ]);
                                    }
                                    $sha = [];
//                                    if (now()->gt($start) && now()->diffInMinutes($start) > 55) {
//                                        $lastExactDate = null;
//                                        throw new \Exception("Stopped. Start: " . $start->toDateTimeString() . ", end: " . now()->toDateTimeString());
//                                    }
                                }
                                if (!$save = Commit::where("repo", $repository->id)
                                    ->where("exact_date", $commitDate)
                                    ->first()
                                ) {
                                    $save = new Commit();
                                    $save->chain = $repository->chain;
                                    $save->repo = $repository->id;
                                    $save->exact_date = $commitDate;
                                    $save->additions = 0;
                                    $save->deletions = 0;
                                }
                                $save->total_commit = 0;
                                $save->author_list = [];

                                $date = $commitDate;
                                $lastExactDate = $commitDate;
                            }
                            $sha[] = $commit->sha;
                            $save->additions += 0;
                            $save->deletions += 0;
                            $save->author_list = array_merge($save->author_list, [$commit->commit->author->name]);
                            $save->total_commit += 1;
                        }
                    }
                }
            } catch (\Exception $exception) {
                Log::error("Chain " . $chain->id . "-" . $chain->name . " have exception: " . $exception->getMessage());
//                    setting()->set("last_repo", $repository->id);
//                    setting()->save();
//                Commit::where("repo", $repository->id)->where("exact_date", $lastExactDate)->delete();
                break;
            }
//                setting()->set("last_repo", $repository->id);
//                setting()->save();
        }
    }
}
