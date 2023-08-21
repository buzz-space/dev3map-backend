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

        $sortByCommit = [];
        $sortByIssue = [];
        $sortByPRSolved = [];
        $sortByDeveloper = [];
        $sortByFork = [];
        $sortByStar = [];
        $chains = Chain::orderBy("id", "ASC")->get();
//        $symbol = ['AKT', 'MNTL', 'AURA', 'AXL', 'BAND', 'BCNA', 'BTSG', 'CANTO', 'HUAHUA', 'CMDX', 'CORE', 'CRE', 'CRO', 'CUDOS', 'DSM', 'NGM', 'EVMOS', 'FET', 'GRAVITION', 'INJ', 'IRIS', 'IXO', 'JUNO', 'KAVA', 'XKI', 'DARC', 'KUJI', 'KYVE', 'LIKE', 'LUM', 'MARS', 'NTRN', 'MED', 'NOBLE', 'NYM', 'FLIX', 'NOM', 'XPRT', 'HASH', 'QSR', 'QCK', 'REGEN', 'ATOLO', 'DVPN', 'SCRT', 'CTK', 'ROWAN', 'SOMM', 'FIS', 'STARS', 'IOV', 'STRD', 'TORI', 'UMEE', 'XPLA', 'FNSA', 'KNOW', '', '', 'BLD', 'ARCH', '', '', 'PLQ', 'LUNA', 'ALEPH', 'ANKR', '', 'SWTH', 'CHEQ', 'CET', '', '', 'CMT', 'DASH', 'DEC', 'DETF', 'TGD', 'XFI', 'DIG', 'MPWR', 'FCT', 'FOAM', 'L1', 'GNOT', 'GARD', 'HiD', 'IDNA', '', 'JKL', 'KDA', 'KIRA', 'KLV', 'MEME', 'NLS', 'NOM', 'ODIN', 'OKT', 'ORAI', '', 'QKC', 'REBUS', '', 'RUNE', 'UPTICK', 'OSMO',];
        foreach ($chains as $chain) {
            $now = ChainInfo::where("chain", $chain->id)->where("range", 0)->first();
            $last7d = ChainInfo::where("chain", $chain->id)->where("range", "7_days")->first();
            $sortByCommit[$chain->id] = $last7d->total_commits;
            $sortByIssue[$chain->id] = $last7d->total_issue_solved;
            $sortByPRSolved[$chain->id] = $last7d->total_pull_merged;
            $sortByDeveloper[$chain->id] = $last7d->part_time_developer;
//            $sortByFork[$chain->id] = $now->total_fork - $last7d->total_fork;
//            $sortByStar[$chain->id] = $now->total_star - $last7d->total_star;
        }

        asort($sortByCommit);
        asort($sortByIssue);
        asort($sortByPRSolved);
        asort($sortByDeveloper);
        asort($sortByFork);
        asort($sortByStar);


        $sortByCommit = array_keys($sortByCommit);
        $sortByIssue = array_keys($sortByIssue);
        $sortByPRSolved = array_keys($sortByPRSolved);
        $sortByDeveloper = array_keys($sortByDeveloper);
        $sortByFork = array_keys($sortByFork);
        $sortByStar = array_keys($sortByStar);

//        Log::info(print_r($sortByCommit, true)); return;

        foreach ($chains as $i => $chain) {
            echo "Chain " . $chain->name . PHP_EOL;
//            if ($chain->id != 4) continue;
//            $developers = Developer::where("chain", $chain->id)->pluck("author")->toArray();
//            $data = process_developer_string(implode(",", $developers));
//            $chain->total_full_time_developer += $data["full_time"];
//            $chain->total_part_time_developer += $data["part_time"];
//            $chain->total_one_time_developer += $data["one_time"];
//            $chain->total_developer += ($data["full_time"] + $data["part_time"] + $data["one_time"]);

            $commit_index = array_search($chain->id, $sortByCommit);
            $pull_index = array_search($chain->id, $sortByPRSolved);
            $issue_index = array_search($chain->id, $sortByIssue);
            $dev_index = array_search($chain->id, $sortByDeveloper);
            $star_index = array_search($chain->id, $sortByStar);
            $fork_index = array_search($chain->id, $sortByFork);
            // Rank
            $chain->commit_rank = $commit_index !== false ? 1 + $commit_index : 101;
            $chain->pull_rank = $pull_index !== false ? 1 + $pull_index : 101;
            $chain->issue_rank = $issue_index !== false ? 1 + $issue_index : 101;
            $chain->dev_rank = $dev_index !== false ? 1 + $dev_index : 101;
            $chain->star_rank = $star_index !== false ? 1 + $star_index : 101;
            $chain->fork_rank = $fork_index !== false ? 1 + $fork_index : 101;
            // Score
            $commit_score = 101 - ($chain->commit_rank > 101 ? 101 : $chain->commit_rank);
            $pull_score = 101 - ($chain->pull_rank > 101 ? 101 : $chain->pull_rank);
            $issue_score = 101 - ($chain->issue_rank > 101 ? 101 : $chain->issue_rank);
            $dev_score = 101 - ($chain->dev_rank > 101 ? 101 : $chain->dev_rank);
            $star_score = 101 - ($chain->star_rank > 101 ? 101 : $chain->star_rank);
            $fork_score = 101 - ($chain->fork_rank > 101 ? 101 : $chain->fork_rank);

            $chain->seriousness = ($commit_score + $issue_score + $pull_score + $dev_score) / 4;
            $chain->rising_star = ($star_score + $fork_score) / 2;
            $chain->ibc_astronaut = ($commit_score + $issue_score + $pull_score) / 3;
//            $chain->symbol = $symbol[$i];
            if ($chain->is_repo)
                $chain->github_prefix = str_replace("/", "-", $chain->github_prefix);
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
