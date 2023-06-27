<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
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
        $begin = "2020-01-01";
        $lastRepo = setting("last_repo", 0);
        $start = now();
        $repositories = Repository::orderBy("id", "ASC")->get();
        foreach ($repositories as $repository) {
            if ($repository->id < $lastRepo) continue;
//                echo "Repository: " . $repository->name . PHP_EOL;
            try {
                $prefix = $repository->github_prefix;
                // Get commit
                $last = now()->toDateString();
                if ($lastCommit = Commit::where("repo", $repository->id)->orderBy("exact_date", "ASC")->first())
                    $last = $lastCommit->exact_date;
                $url = "https://api.github.com/repos/$prefix/commits?per_page=100&since=" . date(DATE_ISO8601, strtotime($begin));
                    if ($last)
                        $url .= "&until=" . date(DATE_ISO8601, strtotime($last));
                    $lastPage = get_last_page(get_github_data($url, "header")); $totalRequest += 1;
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $commitUrl = $url . "&page=$i";
                        $data = json_decode(get_github_data($commitUrl)); $totalRequest += 1;
                        if (isset($data->message) && $data->message == "Git Repository is empty.")
                            break;
//                        echo $commitUrl . PHP_EOL;
                        $date = null;
                        $save = null;
                        foreach ($data as $commit) {
                            $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $commit->sha;
                            $detail = json_decode(get_github_data($detailUrl)); $totalRequest += 1;

                            $commitDate = date("Y-m-d", strtotime($commit->commit->author->date));
                            if ($date != $commitDate) {
                                if ($save) {
                                    $save->author_list = implode(",", $save->author_list);
                                    $save->save();
                                    if (now()->gt($start) && now()->diffInMinutes($start) > 55) {
                                        $lastExactDate = null;
                                        throw new \Exception("Stopped. Start: " . $start->toDateTimeString() . ", end: " . now()->toDateTimeString());
                                    }
                                }
                                $save = new Commit();
                                $save->author_list = [];
                                $save->total_commit = 0;
                                $save->additions = 0;
                                $save->deletions = 0;
                                $save->chain = $repository->chain;
                                $save->repo = $repository->id;
                                $save->exact_date = $commitDate;

                                $date = $commitDate;
                                $lastExactDate = $commitDate;
                            }
                            $save->additions += $detail->stats->additions;
                            $save->deletions += $detail->stats->deletions;
                            $save->author_list = array_merge($save->author_list, [$commit->commit->author->name]);
                            $save->total_commit += 1;
                        }
                    }
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                setting()->set("last_repo", $repository->id);
                setting()->save();
                Commit::where("repo", $repository->id)->where("exact_date", $lastExactDate)->delete();
                break;
            }
            setting()->set("last_repo", $repository->id);
            setting()->save();
        }
    }
}
