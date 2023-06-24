<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $repositories = Repository::all();
        $lastExactDate = "";
        $lastRepo = 2545;
            foreach ($repositories as $repository) {
                if ($repository->id < $lastRepo) continue;
                echo "Repository: " . $repository->name . PHP_EOL;
                $chain = $repository->chain()->first();
                try {
                    $prefix = $repository->github_prefix;

//                // Get repository info
//                $repoUrl = "https://api.github.com/repos/$prefix";
//                $repoInfo = json_decode(get_github_data($repoUrl)); $totalRequest += 1;
////                $repoInfo = get_from_file("repo.json");
//
//                $repository->total_star = $repoInfo->subscribers_count; $chain->total_star += $repository->total_star;
//                $repository->total_fork = $repoInfo->forks_count; $chain->total_fork += $repository->total_fork;
//
//                $issueUrl = "https://api.github.com/repos/$prefix/issues?per_page=100&state=closed";
//                $issueLastPage = get_last_page(get_github_data($issueUrl, "header"));
//                $totalIssueLastPage = count(json_decode(get_github_data($issueUrl . "&page=$issueLastPage"))); $totalRequest += 2;
//                $repository->total_issue_solved = (($issueLastPage - 1) * 100 + $totalIssueLastPage);
//                $chain->total_issue_solved += $repository->total_issue_solved;
//                $chain->save();
//
//                $pullUrl = "https://api.github.com/repos/$prefix/pulls?per_page=100&state=closed";
//                $pullLastPage = get_last_page(get_github_data($pullUrl, "header"));
//                $totalPullLastPage = count(json_decode(get_github_data($pullUrl . "&page=$pullLastPage"))); $totalRequest += 2;
//                $repository->pull_request_closed = (($pullLastPage - 1) * 100 + $totalPullLastPage);
//                $chain->total_pull_request += $repository->pull_request_closed;
//                $chain->save();
//
//                $contributorUrl = "https://api.github.com/repos/$prefix/contributors?per_page=100";
//                $contributorLastPage = get_last_page(get_github_data($contributorUrl, "header"));
//                $contributors = [];
//                for ( $i = 1; $i <= $contributorLastPage; $i++){
//                    $contributors = array_merge($contributors, array_column( (array) json_decode(get_github_data($contributorUrl . "&page=$i")), "login"));
//                }
//                $repository->total_contributor = implode(",", $contributors);
//                $repository->save();

                    // Get commit
                    $begin = "2023-01-01";
                    if ($lastCommit = Commit::where("repo", $repository->id)->orderBy("exact_date", "ASC")->first())
                        $begin = $lastCommit->exact_date;
                    $url = "https://api.github.com/repos/$prefix/commits?per_page=100&since=2023-01-01";
                    if ($begin)
                        $url .= "&until=" . date("Y-m-d", strtotime($begin));
                    $lastPage = get_last_page(get_github_data($url, "header")); $totalRequest += 1;
//                echo "Commit url: " . $url . PHP_EOL;
                    echo "Last page: " . $lastPage . PHP_EOL;
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $commitUrl = $url . "&page=$i";
                        $data = json_decode(get_github_data($commitUrl)); $totalRequest += 1;
                        if (isset($data->message) && $data->message == "Git Repository is empty.")
                            break;
                        echo "Page " . $i . " with " . count($data) . " commits"  . PHP_EOL;
//                    echo print_r($data, true) . PHP_EOL;
//                    $data = get_from_file("commits.json");
                        $date = null;
                        $save = null;
                        foreach ($data as $commit) {
                            $detailUrl = "https://api.github.com/repos/$prefix/commits/" . $commit->sha;
                            $detail = json_decode(get_github_data($detailUrl)); $totalRequest += 1;

                            $commitDate = date("Y-m-d", strtotime($commit->commit->author->date));
                            if ($date != $commitDate) {
                                if ($save) {
                                    $save->author_list = implode(",", $save->author_list);
//                            echo print_r($save, true) . PHP_EOL;
                                    $save->save();
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
                            $save->deletions = $detail->stats->deletions;
                            $save->author_list = array_merge($save->author_list, [$commit->commit->author->name]);
                            $save->total_commit += 1;
                        }
                    }
                } catch (\Exception $exception) {
                    echo $exception->getMessage() . PHP_EOL;
                    echo "Error at repository " . $repository->id . ", at exact date: " . $lastExactDate . PHP_EOL;
                    $lastRepo = $repository->id;

                    sleep(600);
                    break;
                }
//            $choice = $this->choice("Continue?", ["yes", "no"]);
//            if ($choice == "no")
//                break;
            }


        echo "Done";
    }
}
