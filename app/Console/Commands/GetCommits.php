<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainInfo;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\CommitSHA;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;

//use Carbon\Carbon;
use Carbon\Carbon;
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
        set_time_limit(0);
        $lastExactDate = null;
        $last = "2018-01-01";
        $lastRepo = setting("last_repo", 0);
        $start = now();
        $chainId = 11;
        foreach (Chain::orderBy("id", "ASC")->get() as $chain) {
            if ($chain->id < $chainId) continue;
            echo "Chain: " . $chain->name . PHP_EOL;
            $repositories = Repository::where("chain", $chain->id)->orderBy("id", "ASC")->get();
            echo "With " . count($repositories) . PHP_EOL;
            try {
                foreach ($repositories as $j => $repository) {
//                    if ($chain->id == $chainId && $repository->id < $repoId) continue;
                    /**
                     * Get commits
                     */
                    echo ($j + 1) . " (" . $chain->id . "): " . $repository->id . "-" . $repository->name . PHP_EOL;
                    $contributors = unique_name(explode(",", $repository->total_contributor));
                    $prefix = $repository->github_prefix;
                    if ($lastCommit = Commit::where("repo", $repository->id)->orderBy("exact_date", "DESC")->first())
                        $last = "2023-09-01";
                    else {
                        echo "Repository has no commit!" . PHP_EOL;
                        continue;
                    }
                    $until = now()->toDateString();
                    $url = "https://api.github.com/repos/$prefix/commits?per_page=100";
                    $url .= "&since=" . date(DATE_ISO8601, strtotime($last));
                    $url .= "&until=" . date(DATE_ISO8601, strtotime($until));
                    $lastPage = get_last_page(get_github_data($url, "header"));
                    echo "Total page: " . $lastPage . PHP_EOL;
                    for ($i = 1; $i <= $lastPage; $i++) {
//                        if ($chain->id == $chainId && $repository->id == $repoId && $i < $page) continue;
//                    $i = 1;
                        echo "Process page $i..." . PHP_EOL;
                        $commitUrl = $url . "&page=$i";
                        $data = json_decode(get_github_data($commitUrl));
                        if (isset($data->message)) {
                            Log::info("Repository " . $repository->name . ": " . $data->message);
                            continue;
                        }
                        $date = null;
                        $save = null;
                        $sha = [];
                        foreach ($data as $commit) {
                            if (strpos($commit->commit->message, "Merge pull request") === 0)
                                continue;
                            if (isset($commit->author))
                                $author = $commit->author->login ?? "";
                            else
                                $author = $commit->commit->author->name;
                            if (!in_array($author, $contributors))
                                continue;
                            $commitDate = date("Y-m-d", strtotime($commit->commit->author->date));
                            if ($date != $commitDate) {
                                if ($save) {
                                    $save->author_list = implode(",", $save->author_list);
                                    $save->save();

                                    // save sha
                                    $exists = CommitSHA::where("commit_id", $save->id)->pluck("sha")->toArray();
                                    $sha = array_filter($sha, function ($row) use ($exists) {
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
                            $save->author_list = array_merge($save->author_list, [$author]);
                            $save->total_commit += 1;
                        }
                    }

                    /**
                     * Summarize Developer
                     */
                    $cms = Commit::where("chain", $chain->id)
                        ->where("repo", $repository->id)
                        ->where("exact_date", ">=", $last)
                        ->orderBy("exact_date", "ASC")
                        ->get();
                    foreach ($cms as $item) {
                        $day = Carbon::createFromTimestamp(strtotime($item->exact_date));
                        $currentAuthor = array_filter(explode(",", $item->author_list));
                        if (empty($currentAuthor)) continue;
                        $last30Day = (clone $day)->addDays(-30);
                        $lastAuthor = Commit::where("chain", $chain->id)
                            ->where("exact_date", "<", $day->toDateString())
                            ->where("exact_date", ">=", $last30Day->toDateString())
                            ->pluck("author_list")->toArray();
                        $lastAuthor = explode(",", implode(",", $lastAuthor));

                        $authors = array_count_values($currentAuthor);
                        $last30DayAuthors = array_count_values($lastAuthor);
                        $full = 0;
                        $part = 0;
                        $one = 0;
                        $totalCommit = 0;
                        $saving = [
                            "full_time" => [],
                            "part_time" => [],
                            "one_time" => []
                        ];
                        foreach ($authors as $author => $commits) {
                            if (isset($last30DayAuthors[$author]))
                                $commits += $last30DayAuthors[$author];
                            if ($commits > 10) {
                                $full += 1;
                                $saving["full_time"][] = $author;
                            }
                            if ($commits <= 10 && $commits > 1) {
                                $part += 1;
                                $saving["part_time"][] = $author;
                            }
                            if ($commits == 1) {
                                $one += 1;
                                $saving["one_time"][] = $author;
                            }

                            $totalCommit += $commits;
                        }

                        $item->full_time = implode(",", $saving["full_time"]);
                        $item->part_time = implode(",", $saving["part_time"]);
                        $item->total_full_time = count($saving["full_time"]);
                        $item->total_part_time = count($saving["part_time"]);
                        $item->save();
                    }

                    /**
                     * Apply to chart
                     */

                    $until = Carbon::createFromTimestamp(strtotime($until));
                    $week = 1;
                    $from = Carbon::create($until->year, $until->month, 1);
                    $to = Carbon::create($until->year, $until->month, 15);
                    if ($until->day > 15) {
                        $week = 2;
                        $from = Carbon::create($until->year, $until->month, 16);
                        $to = Carbon::create($until->year, $until->month, $until->daysInMonth);
                    }
                    if (!$exist = CommitChart::where([
                        ["week", $week],
                        ["month", $until->month],
                        ["year", $until->year],
                        ["chain", $repository->chain]
                    ])->first()) {
                        $exist = new CommitChart();
                        $exist->week = $week;
                        $exist->month = $until->month;
                        $exist->year = $until->year;
                        $exist->chain = $repository->chain;
                        $exist->from = $from->toDateString();
                        $exist->to = $to->toDateString();
                        $exist->week = $week;
                        $exist->save();
                    }

                    $toChart = (clone $cms)->toArray();
                    $exist->total_commit += array_sum(array_column($toChart, "total_commit"));
                    $exist->total_additions += array_sum(array_column($toChart, "additions"));
                    $exist->total_deletions += array_sum(array_column($toChart, "deletions"));
                    $exist->save();
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

        echo "It's take " . now()->diffInMinutes($start) . " minutes!" . PHP_EOL;
    }
}
