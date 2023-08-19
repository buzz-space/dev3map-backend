<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;
use RvMedia;

class GetRepositories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:repositories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get repositories';

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
        set_time_limit(0);
        $chains = Chain::orderBy("id", "DESC")->get();
        $chainId = 4;
        $repoId = 94;
        foreach ($chains as $i => $chain) {
            if ($chain->id != $chainId) continue;
            echo "Chain " . $chain->name . PHP_EOL;
            try {
                if (!$chain->is_repo) {
                    $chainUrl = "https://api.github.com/orgs/" . $chain->github_prefix;
                    $chainInfo = json_decode(get_github_data($chainUrl));
                    if (isset($chainInfo->message))
                        throw new \Exception($chainInfo->message);

                    $chain->avatar = $chainInfo->avatar_url ? $chainInfo->avatar_url : null;
                    $chain->name = $chainInfo->name ?? ucfirst(utf8convert($chain->login));
                    $chain->website = $chainInfo->blog ?? "";
                    $chain->description = $chainInfo->description ?? "";
//                $chain->categories = $categories[$i];
                    // Get all repository from chain (test aura-nw)
                    $prefix = $chain->github_prefix;
                    $url = "https://api.github.com/orgs/$prefix/repos?per_page=100";
                    $lastPage = get_last_page(get_github_data($url, "header"));
                    $repository = [];
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $repository = array_merge($repository, array_column((array)json_decode(get_github_data($url . "&page=$i")), "full_name", "name"));
                    }
                } else
                    $repository = [$chain->name => $chain->github_prefix];

                echo "With " . count($repository) . PHP_EOL;
                foreach ($repository as $name => $repoPrefix) {
                    $repoUrl = "https://api.github.com/repos/$repoPrefix";
                    echo "Repo " . $repoUrl . " of chain " . $chain->name . PHP_EOL;
                    $repoInfo = json_decode(get_github_data($repoUrl));
                    if (isset($repoInfo->message)){
                        if (strpos($repoInfo->message, "API rate limit exceeded"))
                            throw new \Exception($repoInfo->message);
                        continue;
                    }
                    if (!$repo = Repository::where("github_prefix", $repoPrefix)->first()) {
                        $repo = new Repository();
                        $repo->name = $name;
                        $repo->github_prefix = $repoPrefix;
                        $repo->chain = $chain->id;
                        $repo->save();
                    }
                    echo "Repo id " . $repo->id . ":" . $chain->id . PHP_EOL;
                    if ($chain->id == $chainId && $repo->id != $repoId) continue;

                    // Contributors
                    $url = "https://api.github.com/repos/$repoPrefix/contributors?per_page=100";
                    $lastPage = get_last_page(get_github_data($url, "header", 2));
                    $contributors = [];
                    echo "Get contributors!" . PHP_EOL;
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $pageUrl = $url . "&page=$i";
                        $data = json_decode(get_github_data($pageUrl, "body", 2));
                        if (isset($data->message))
                            throw new \Exception($data->message);

                        $contributors += array_column((array)$data, "login");
                    }
                    Log::info(print_r($contributors, true));

                    // Fork contributor
                    $repo->total_star = $repoInfo->stargazers_count;
                    $repo->total_fork = $repoInfo->forks_count;
                    $repo->subscribers = $repoInfo->subscribers_count;
                    $repo->description = $repoInfo->description;
                    if ($repoInfo->fork) {
                        $cUrl = "https://api.github.com/repos/" . $repoInfo->parent->full_name . "/contributors?per_page=100";
                        $parentContributors = array_column((array)json_decode(get_github_data($cUrl, "body", 2)), "login");

                        $contributors = array_filter($contributors, function ($row) use ($parentContributors) {
                            return !in_array($row, $parentContributors);
                        });
                    }
                    Log::info(print_r($contributors, true));
                    $repo->total_contributor = implode(",", $contributors);
                    $repo->contributors = count($contributors);

//                    // Issue
//                    $url = "https://api.github.com/repos/$repoPrefix/issues?per_page=100&state=closed";
//                    if ($lastIssue = Issue::where("repo", $repo->id)->orderBy("open_date", "DESC")->first())
//                        $url .= ("&since=" . date(DATE_ISO8601, strtotime($lastIssue->open_date)));
//                    $lastPage = get_last_page(get_github_data($url, "header", 2));
//                    echo "Get issue solved with $lastPage page!" . PHP_EOL;
//                    $total_issue = 0;
//                    for ($i = 1; $i <= $lastPage; $i++) {
//                        $pageUrl = $url . "&page=$i";
//                        $data = json_decode(get_github_data($pageUrl, "body", 2));
//                        if (isset($data->message))
//                            throw new \Exception($data->message);
//
//                        foreach ($data as $issue) {
//                            $total_issue += 1;
//                            if (!$exist = Issue::where("issue_id", $issue->id)->first()) {
//                                $open = Carbon::createFromTimestamp(strtotime($issue->created_at));
//                                $closed = Carbon::createFromTimestamp(strtotime($issue->closed_at));
//
//                                Issue::create([
//                                    "issue_id" => $issue->id,
//                                    "creator" => $issue->user->login,
//                                    "repo" => $repo->id,
//                                    "chain" => $repo->chain,
//                                    "open_date" => $open->toDateTimeString(),
//                                    "close_date" => $closed->toDateTimeString(),
//                                    "total_minute" => $closed->diffInMinutes($open)
//                                ]);
//                            }
//                        }
//                    }
//                    $repo->total_issue_solved = $total_issue;
//
//                    // Pull
//                    $url = "https://api.github.com/repos/$repoPrefix/pulls?per_page=100&state=all";
//                    if ($lastPull = Pull::where("repo", $repo->id)->orderBy("created_date", "DESC")->first())
//                        $url .= ("&since=" . date(DATE_ISO8601, strtotime($lastPull->created_date)));
//                    $lastPage = get_last_page(get_github_data($url, "header", 2));
//                    echo "Get pull request with $lastPage page!" . PHP_EOL;
//                    $total_pulls = 0;
//                    for ($i = 1; $i <= $lastPage; $i++) {
//                        $pageUrl = $url . "&page=$i";
//                        $data = json_decode(get_github_data($pageUrl, "body", 2));
//                        if (isset($data->message))
//                            throw new \Exception($data->message);
//
//                        foreach ($data as $pull) {
//                            if ($pull->state == "closed")
//                                $total_pulls += 1;
//                            if (!$exist = Pull::where("pull_id", $pull->id)->first()) {
//                                Pull::create([
//                                    "pull_id" => $pull->id,
//                                    "author" => $pull->user->login,
//                                    "status" => $pull->state,
//                                    "repo" => $repo->id,
//                                    "chain" => $repo->chain,
//                                    "created_date" => date("Y-m-d H:i:s", strtotime($pull->created_at)),
//                                ]);
//                            } else {
//                                $exist->created_date = date("Y-m-d H:i:s", strtotime($pull->created_at));
//                                $exist->save();
//                            }
//                        }
//                    }
//                    $repo->pull_request_closed = $total_pulls;

                    $repo->created_date = date("Y-m-d H:i:s", strtotime($repoInfo->created_at));
                    $repo->is_fork = $repoInfo->fork;
                    $repo->save();

                    $chain->subscribers += $repoInfo->subscribers_count;
                }

                $chain->last_updated = now();
                $chain->save();
            } catch (\Exception $exception) {
                echo $exception->getMessage() . PHP_EOL;
                break;
            }
        }


        echo "Done" . PHP_EOL;
    }
}
