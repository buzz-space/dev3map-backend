<?php

namespace Botble\Statistic\Jobs;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetInfoChain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chain_id;
    protected $timeout = -1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chain_id)
    {
        $this->chain_id = $chain_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $chain = Chain::where("id", $this->chain_id)->first();
            echo "Fetching info from chain " . $chain->name . PHP_EOL;
            $chainUrl = "https://api.github.com/orgs/" . $chain->github_prefix;
            $chainInfo = json_decode(get_github_data($chainUrl));
            $chain->avatar = $chainInfo->avatar_url ? $chainInfo->avatar_url : null;
            $chain->name = $chainInfo->name ?? ucfirst(utf8convert($chain->login));
            $chain->website = $chainInfo->blog;
            $chain->description = $chainInfo->description;
            // Get all repository from chain (test aura-nw)
            $prefix = $chain->github_prefix;
            $url = "https://api.github.com/orgs/$prefix/repos?per_page=100";
            $lastPage = get_last_page(get_github_data($url, "header"));
            $repository = [];
            for ($i = 1; $i <= $lastPage; $i++) {
                $repository = array_merge($repository, array_column((array)json_decode(get_github_data($url . "&page=$i")), "full_name", "name"));
            }
            $chain->save();
            echo "About " . count($repository) . " repositories!";

            foreach ($repository as $name => $repoPrefix) {
                $repoUrl = "https://api.github.com/repos/$repoPrefix";
                echo "Repo " . $repoUrl . " of chain " . $chain->name . PHP_EOL;
                $repoInfo = json_decode(get_github_data($repoUrl));
                if (isset($repoInfo->message))
                    throw new \Exception($repoInfo->message);
                if (!$repo = Repository::where("github_prefix", $repoPrefix)->first()) {
                    $repo = new Repository();
                    $repo->name = $name;
                    $repo->github_prefix = $repoPrefix;
                    $repo->chain = $chain->id;
                    $repo->save();
                }
//                if ($chain->id == 11 && $repo->id < 498) continue;

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

                // Fork contributor
                $infoUrl = "https://api.github.com/repos/$repoPrefix";
                $info = json_decode(get_github_data($infoUrl, "body", 2));
                $repo->total_star = $info->stargazers_count;
                $repo->total_fork = $info->forks_count;
                $repo->subscribers = $info->subscribers_count;
                if ($info->fork) {
                    echo "Repo " . $repo->name . " has fork is " . $info->parent->name . PHP_EOL;
                    $cUrl = "https://api.github.com/repos/" . $info->parent->full_name . "/contributors?per_page=100";
                    $parentContributors = array_column((array)json_decode(get_github_data($cUrl, "body", 2)), "login");

                    $contributors = array_filter($contributors, function ($row) use ($parentContributors) {
                        return !in_array($row, $parentContributors);
                    });
                }
                if (!$exist = Contributor::where("repo", $repo->id)->first()) {
                    Contributor::create([
                        "repo" => $repo->id,
                        "contributors" => implode(",", $contributors),
                        "chain" => $repo->chain
                    ]);
                } else {
                    $exist->contributors = implode(",", $contributors);
                    $exist->save();
                }

                // Issue
                $url = "https://api.github.com/repos/$repoPrefix/issues?per_page=100&state=closed";
                $lastPage = get_last_page(get_github_data($url, "header", 2));
                echo "Get issue of $lastPage pages!" . PHP_EOL;
                $total_issue = 0;
                for ($i = 1; $i <= $lastPage; $i++) {
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl, "body", 2));
                    if (isset($data->message))
                        throw new \Exception($data->message);

                    foreach ($data as $issue) {
                        $total_issue += 1;
                        if (!$exist = Issue::where("issue_id", $issue->id)->first()) {
                            $open = Carbon::createFromTimestamp(strtotime($issue->created_at));
                            $closed = Carbon::createFromTimestamp(strtotime($issue->closed_at));

                            Issue::create([
                                "issue_id" => $issue->id,
                                "creator" => $issue->user->login,
                                "repo" => $repo->id,
                                "chain" => $repo->chain,
                                "open_date" => $open->toDateTimeString(),
                                "close_date" => $closed->toDateTimeString(),
                                "total_minute" => $closed->diffInMinutes($open)
                            ]);
                        }
                    }
                }
                $repo->total_issue_solved = $total_issue;

                // Pull
                $url = "https://api.github.com/repos/$repoPrefix/pulls?per_page=100";
                $lastPage = get_last_page(get_github_data($url, "header", 2));
                echo "Get pulls of $lastPage pages!" . PHP_EOL;
                $total_pulls = 0;
                for ($i = 1; $i <= $lastPage; $i++) {
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl, "body", 2));
                    if (isset($data->message))
                        throw new \Exception($data->message);

                    foreach ($data as $pull) {
                        $total_pulls += 1;
                        if (!$exist = Pull::where("pull_id", $pull->id)->first()) {
                            Pull::create([
                                "pull_id" => $pull->id,
                                "author" => $pull->user->login,
                                "status" => $pull->state,
                                "repo" => $repo->id,
                                "chain" => $repo->chain,
                                "created_date" => date("Y-m-d H:i:s", strtotime($pull->created_at)),
                            ]);
                        } else {
                            $exist->created_date = date("Y-m-d H:i:s", strtotime($pull->created_at));
                            $exist->save();
                        }
                    }
                }
                $repo->pull_request_closed = $total_pulls;

                $repo->created_date = date("Y-m-d H:i:s", strtotime($repoInfo->created_at));
                $repo->is_fork = $repoInfo->fork;
                $repo->save();

                $chain->subscribers += $repoInfo->subscribers_count;
            }
            $chain->last_updated = now();
            $chain->save();
        } catch (\Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }
}
