<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Developer;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
    public function fHandle()
    {
        $sortByCommit = Chain::orderBY("total_commit", "DESC")->pluck("id")->toArray();
        $sortByIssue = Chain::orderBY("total_issue_solved", "DESC")->pluck("id")->toArray();
        $sortByPRSolved = Chain::orderBY("total_pull_request", "DESC")->pluck("id")->toArray();
        $sortByDeveloper = Chain::orderBY("total_developer", "DESC")->pluck("id")->toArray();
        $sortByFork = Chain::orderBY("total_fork", "DESC")->pluck("id")->toArray();
        $sortByStar = Chain::orderBY("total_star", "DESC")->pluck("id")->toArray();
        $chains = Chain::all();
        foreach ($chains as $chain) {
            echo "Chain " . $chain->name . PHP_EOL;
            if ($chain->id <= 60) continue;
//            $developers = Developer::where("chain", $chain->id)->pluck("author")->toArray();
//            $data = process_developer_string(implode(",", $developers));
//            $chain->total_full_time_developer += $data["full_time"];
//            $chain->total_part_time_developer += $data["part_time"];
//            $chain->total_one_time_developer += $data["one_time"];
//            $chain->total_developer += ($data["full_time"] + $data["part_time"] + $data["one_time"]);

            $commitRank = count($chains) - array_search($chain->id, $sortByCommit) + 1;
            $issueRank = count($chains) - array_search($chain->id, $sortByIssue) + 1;
            $PRSolvedRank = count($chains) - array_search($chain->id, $sortByPRSolved) + 1;
            $developerRank = count($chains) - array_search($chain->id, $sortByDeveloper) + 1;
            $forkRank = count($chains) - array_search($chain->id, $sortByFork) + 1;
            $starRank = count($chains) - array_search($chain->id, $sortByStar) + 1;
            $chain->seriousness = round($commitRank / 100 * 35, 2) + round($issueRank / 100 * 20, 2)
                + round($PRSolvedRank / 100 * 20, 2) + round($developerRank / 100 * 25, 2);
            $chain->rising_star = round($forkRank / 100 * 65, 2) + round($starRank / 100 * 35, 2);
            $chain->ibc_astronaut = round($commitRank / 100 * 50, 2) + round($issueRank / 100 * 20, 2)
                + round($PRSolvedRank / 100 * 30, 2);
            $chain->save();

        }

        echo "Done";
    }

    public function handle()
    {
        $chain = Chain::find(4);
//        foreach (Chain::all() as $chain) {
            echo "Chain " . $chain->name . PHP_EOL;
//            if ($chain->id <= 60) continue;
            // Summarize contributor
//            $chainContributor = $chain->repositories()->pluck("total_contributor");
//            $contributors = [];
//            foreach ($chainContributor as $c) {
//                $contributors = array_merge($contributors, explode(",", $c));
//            }
//            $chain->total_contributor = count(array_unique($contributors));

            // Summarize Commit
//            $chain->total_commit = Commit::where("chain", $chain->id)->sum("total_commit");
//            if ($chain->total_commit == 0)
//                continue;

            // Summarize developer
            $date = Commit::where("chain", $chain->id)
//                ->where("exact_date", ">=", "2023-04-01")
//                ->where("exact_date", "<", "2023-06-01")
                ->orderBy("exact_date", "ASC")
                ->pluck("exact_date")->toArray();
            foreach(array_unique($date) as $day){
                $day = Carbon::createFromTimestamp(strtotime($day));
//                echo "Day " . $day->toDateString() . PHP_EOL;
                $currentAuthor = Commit::where("chain", $chain->id)
                    ->where("exact_date", $day->toDateString())
                    ->pluck("author_list")->toArray();
//                Log::info(print_r($currentAuthor, true));
                $currentAuthor = array_filter(explode(",", implode(",", $currentAuthor)));
//                Log::info("After: " . implode(",", $currentAuthor));
//                if ($day->toDateString() == "2020-01-08") return;
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

                $exist = Developer::where("day", $day->toDateString())->where("chain", $chain->id)->first();
                if (!$exist) {
                    Developer::create([
                        "chain" => $chain->id,
                        "total_full_time" => $full,
                        "total_part_time" => $part,
                        "total_one_time" => $one,
                        "total_developer" => $full + $part + $one,
                        "total_commit" => $totalCommit,
                        "author" => implode(",", $currentAuthor),
                        'day' => $day->toDateString(),
                        "full_time" => implode(",", $saving["full_time"]),
                        "part_time" => implode(",", $saving["part_time"]),
                        "one_time" => implode(",", $saving["one_time"]),
                    ]);

                    echo "Created developer info in " . $day->toDateString() . PHP_EOL;
                }
            }

//            $choice = $this->choice("Continue?", ["no", "yes"], "no");
//            if ($choice == "no")
//                break;

//            $diff = $dateFirstCommit->diffInMonths($dateLastCommit) + ($dateFirstCommit->day > $dateLastCommit->day ? 2 : 1);
//            for ($i = 0; $i < $diff; $i++) {
//                $exactMonth = (clone $dateFirstCommit)->addMonths($i);
//                echo "Month " . $exactMonth->month . ", year: " . $exactMonth->year . PHP_EOL;
//                $authors = Commit::where("chain", $chain->id)
//                    ->where("exact_date", ">=", $exactMonth->firstOfMonth()->toDateTimeString())
//                    ->where("exact_date", "<", $exactMonth->endOfMonth()->toDateTimeString())
//                    ->pluck("author_list")->toArray();
//                $devs = [];
//                foreach ($authors as $author) {
//                    $lst = array_count_values(explode(",", $author));
//                    foreach ($lst as $key => $item) {
//                        if (isset($devs[$key]))
//                            $devs[$key] += $item;
//                        else
//                            $devs[$key] = $item;
//                    }
//                }
//
////                write_to_file("devs.txt", print_r($devs, true)); return;
//
//                if (!$d = Developer::where("chain", $chain->id)
//                    ->where("month", $exactMonth->month)
//                    ->where("year", $exactMonth->year)->first()
//                ) {
//                    $d = new Developer();
//                    $d->chain = $chain->id;
//                    $d->month = $exactMonth->month;
//                    $d->year = $exactMonth->year;
//                }
//                $d->author = implode(',', $authors);
//                $d->total_developer = count($devs);
//                $d->total_commit = 0;
//                foreach ($devs as $dev => $commit_count) {
////                    echo "Dev " . $dev . " with " . $commit_count . " commits" . PHP_EOL;
//                    if ($commit_count == 1)
//                        $d->total_one_time += 1;
//                    if ($commit_count > 1 && $commit_count <= 10)
//                        $d->total_part_time += 1;
//                    if ($commit_count > 10)
//                        $d->total_full_time += 1;
//                    $d->total_commit += $commit_count;
//                }
//
//                $d->save();
////            }
//
//                $chain->save();
//

//            }
//            break;
//        }
        echo "Done";
    }

    public function handles()
    {
        $repositories = Repository::orderBy("id", "ASC")->get();
        foreach ($repositories as $repository) {
            echo $repository->name . PHP_EOL;
            $prefix = $repository->github_prefix;
            $url = "https://api.github.com/repos/$prefix";
            $detail = json_decode(get_github_data($url));
            if (isset($detail->message))
                continue;
            $repository->total_star = $detail->stargazers_count;
            $repository->total_fork = $detail->forks_count;

            $issueUrl = "https://api.github.com/repos/$prefix/issues?per_page=100&state=closed";
            $issueLastPage = get_last_page(get_github_data($issueUrl, "header"));
            $totalIssueLastPage = count(json_decode(get_github_data($issueUrl . "&page=$issueLastPage")));
            $repository->total_issue_solved = (($issueLastPage - 1) * 100 + $totalIssueLastPage);

            $issueUrl = "https://api.github.com/repos/$prefix/pulls?per_page=100&state=closed";
            $issueLastPage = get_last_page(get_github_data($issueUrl, "header"));
            $totalIssueLastPage = count(json_decode(get_github_data($issueUrl . "&page=$issueLastPage")));
            $repository->pull_request_closed = (($issueLastPage - 1) * 100 + $totalIssueLastPage);

            $contributorUrl = "https://api.github.com/repos/$prefix/contributors?per_page=100";
            $contributorLastPage = get_last_page(get_github_data($contributorUrl, "header"));
            $contributors = [];
            for ($i = 1; $i <= $contributorLastPage; $i++) {
                $contributors = array_merge($contributors, array_column((array)json_decode(get_github_data($contributorUrl . "&page=$i")), "login"));
            }
            $repository->total_contributor = implode(",", $contributors);

            $repository->save();
        }
    }


}
