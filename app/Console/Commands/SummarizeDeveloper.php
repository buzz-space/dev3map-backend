<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainInfo;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Developer;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SummarizeDeveloper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:developer';

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
        $sortByCommit = ChainInfo::where("range", "24_hours")->orderBy("total_commits", "DESC")->pluck("chain")->toArray();
        $sortByIssue = ChainInfo::where("range", "24_hours")->orderBy("total_issue_solved", "DESC")->pluck("chain")->toArray();
        $sortByPRSolved = ChainInfo::where("range", "24_hours")->orderBy("total_pull_merged", "DESC")->pluck("chain")->toArray();
        $sortByDeveloper = ChainInfo::where("range", "24_hours")
            ->selectRaw("chain, (full_time_developer + part_time_developer) as total_developer")
            ->orderBy("total_developer", "DESC")->pluck("chain")->toArray();
        $sortByFork = ChainInfo::where("range", "24_hours")->orderBy("total_fork", "DESC")->pluck("chain")->toArray();
        $sortByStar = ChainInfo::where("range", "24_hours")->orderBy("total_star", "DESC")->pluck("chain")->toArray();
        $chains = Chain::orderBy("id", "ASC")->get();
        foreach ($chains as $chain) {
            echo "Chain " . $chain->name . PHP_EOL;
//            if ($chain->id != 4) continue;
//            $developers = Developer::where("chain", $chain->id)->pluck("author")->toArray();
//            $data = process_developer_string(implode(",", $developers));
//            $chain->total_full_time_developer += $data["full_time"];
//            $chain->total_part_time_developer += $data["part_time"];
//            $chain->total_one_time_developer += $data["one_time"];
//            $chain->total_developer += ($data["full_time"] + $data["part_time"] + $data["one_time"]);

            $chain->commit_rank = array_search($chain->id, $sortByCommit) + 1;
            $chain->pull_rank = array_search($chain->id, $sortByPRSolved) + 1;
            $chain->issue_rank = array_search($chain->id, $sortByIssue) + 1;
            $chain->dev_rank = array_search($chain->id, $sortByDeveloper) + 1;
            $chain->star_rank = array_search($chain->id, $sortByStar) + 1;
            $chain->fork_rank = array_search($chain->id, $sortByFork) + 1;
            $commitRank = count($chains) - $chain->commit_rank;
            $issueRank = count($chains) - $chain->issue_rank;
            $PRSolvedRank = count($chains) - $chain->pull_rank;
            $developerRank = count($chains) - $chain->dev_rank;
            $forkRank = count($chains) - $chain->fork_rank;
            $starRank = count($chains) - $chain->star_rank;
            $chain->seriousness = (round($commitRank / 100 * 35, 2) + round($issueRank / 100 * 20, 2)
                + round($PRSolvedRank / 100 * 20, 2) + round($developerRank / 100 * 25, 2)) / 4;
            $chain->rising_star = (round($forkRank / 100 * 65, 2) + round($starRank / 100 * 35, 2)) / 2;
            $chain->ibc_astronaut = (round($commitRank / 100 * 50, 2) + round($issueRank / 100 * 20, 2)
                + round($PRSolvedRank / 100 * 30, 2)) / 3;
            $chain->save();
            echo PHP_EOL;
        }

        echo "Done";
    }

    public function handles()
    {
//        $chain = Chain::find($this->ask("Chain id?"));
        foreach (Chain::all() as $chain) {
            echo "Chain: " . $chain->name . PHP_EOL;
//            if ($chain->id != 40) continue;
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
            $repos = Repository::where("chain", $chain->id)->get();
            foreach ($repos as $repo) {
                echo "Repo: " . $repo->name . PHP_EOL;
                $cms = Commit::where("chain", $chain->id)
                    ->where("repo", $repo->id)
                    ->orderBy("exact_date", "ASC")
                    ->get();
                foreach ($cms as $item) {
                    $day = Carbon::createFromTimestamp(strtotime($item->exact_date));
//                echo "Day " . $day->toDateString() . PHP_EOL;
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
            }
            echo PHP_EOL;
        }

        echo "Done";
    }

    public function handless()
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
