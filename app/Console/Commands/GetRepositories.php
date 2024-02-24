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
use RvMedia;

class GetRepositories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:repositories {start_chain} {start_repo} {end_chain}';

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
        \Log::info("Begin get repositories at " . now("Asia/Bangkok")->toDateTimeString());
        set_time_limit(0);
        $chainId = $this->argument("start_chain") ?? 0;
        $repoId = $this->argument("start_repo") ?? 0;
        $toChain = $this->argument("end_chain") ?? 0;
        echo "Start: $chainId, end: $toChain, start repo: $repoId" . PHP_EOL;
//        $howToGet = $this->choice("Choose data want to get?", ["all", "contributor", "pull_issue"], "all");
        $howToGet = "all";
//        $useKey = $this->ask("Use key?");
        $useKey = 1;
        $chains = Chain::orderBy("id", "ASC");
        if ($toChain > 0)
            $chains->where("id", "<=", $toChain);
        $chains = $chains->get();

        foreach ($chains as $i => $chain) {
            if ($chain->id < $chainId) continue;
//            setting()->set("process_chain", $chain->id);
//            setting()->save();
            echo "Chain " . $chain->name . PHP_EOL;
            try {

                if (!$chain->is_repo) {
                    $chainUrl = "https://api.github.com/orgs/" . $chain->github_prefix;
                    $chainInfo = json_decode(get_github_data($chainUrl, 1, $useKey));
                    if (isset($chainInfo->message))
                        throw new \Exception($chainInfo->message);

                    $chain->avatar = $chainInfo->avatar_url ? $chainInfo->avatar_url : null;
//                    $chain->name = $chainInfo->name ?? ucfirst(utf8convert($chain->login));
                    $chain->website = $chainInfo->blog ?? "";
//                    $chain->description = $chainInfo->description ?? "";
//                $chain->categories = $categories[$i];
                    // Get all repository from chain (test aura-nw)
                    $prefix = $chain->github_prefix;
                    $url = "https://api.github.com/orgs/$prefix/repos?per_page=100";
                    $lastPage = get_last_page(get_github_data($url, 0, $useKey));
                    if ($howToGet != "pull") {
                        $repository = [];
                        for ($i = 1; $i <= $lastPage; $i++) {
                            $repository = array_merge($repository, array_column((array)json_decode(get_github_data($url . "&page=$i", 1, $useKey)), "full_name", "name"));
                        }
                    } else {
                        $repository = Repository::where("chain", $chain->id)->orderBy("id", "ASC")->get();
                    }
                } else
                    $repository = [$chain->name => $chain->github_prefix];

                echo "With " . count($repository) . PHP_EOL;
                foreach ($repository as $name => $repoPrefix) {
                    if (strpos($repoPrefix, "chromium") !== false || strpos($repoPrefix, "linux") !== false) continue;
//                    setting()->set("process_repo", $repoPrefix);
//                    setting()->save();
                    $repoUrl = "https://api.github.com/repos/$repoPrefix";
                    echo "Repo " . $repoUrl . " of chain " . $chain->name . PHP_EOL;
                    $repoInfo = json_decode(get_github_data($repoUrl, 1, $useKey));
                    if (isset($repoInfo->message)) {
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
                    if ($chain->id == $chainId && $repo->id < $repoId) continue;

                    if ($howToGet != "pull_issue") {
                        // Contributors
                        $url = "https://api.github.com/repos/$repoPrefix/contributors?per_page=100";
                        $lastPage = get_last_page(get_github_data($url, 0, $useKey));
                        $contributors = [];
                        echo "Get contributors!" . PHP_EOL;
                        for ($i = 1; $i <= $lastPage; $i++) {
                            $pageUrl = $url . "&page=$i";
                            $data = json_decode(get_github_data($pageUrl, 1, $useKey));
                            if (isset($data->message))
                                throw new \Exception($data->message);

                            $contributors += array_column((array)$data, "login");
                        }
                        $contributors = array_filter($contributors, function ($row) {
                            return strpos($row, "[bot]") === false;
                        });

                        // Fork contributor
                        $repo->total_star = $repoInfo->stargazers_count;
                        $repo->total_fork = $repoInfo->forks_count;
                        $repo->subscribers = $repoInfo->subscribers_count;
                        $repo->description = $repoInfo->description;
                        if ($repoInfo->fork) {
                            $cUrl = "https://api.github.com/repos/" . $repoInfo->parent->full_name . "/contributors?per_page=100";
                            $parentContributors = array_column((array)json_decode(get_github_data($cUrl, 1, $useKey)), "login");

                            $contributors = array_filter($contributors, function ($row) use ($parentContributors) {
                                return !in_array($row, $parentContributors);
                            });
                        }
                        $repo->total_contributor = implode(",", $contributors);
                        $repo->contributors = count($contributors);
                    }

                    if ($howToGet != "contributor") {
                        // Issue
                        $url = "https://api.github.com/repos/$repoPrefix/issues?per_page=100&state=closed";
                        if ($lastIssue = Issue::where("repo", $repo->id)->orderBy("open_date", "DESC")->first())
                            $url .= ("&since=" . date(DATE_ISO8601, strtotime($lastIssue->open_date)));
                        $lastPage = get_last_page(get_github_data($url, 0, $useKey));
                        echo "Get issue solved with $lastPage page!" . PHP_EOL;
                        $total_issue = 0;
                        for ($i = 1; $i <= $lastPage; $i++) {
                            $pageUrl = $url . "&page=$i";
                            $data = json_decode(get_github_data($pageUrl, 1, $useKey));
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
                                        "open_date" => $open->startOfDay()->toDateTimeString(),
                                        "close_date" => $closed->startOfDay()->toDateTimeString(),
                                        "total_minute" => $closed->diffInMinutes($open)
                                    ]);
                                }
                            }
                        }
                        $repo->total_issue_solved = $total_issue;

                        // Pull
                        $url = "https://api.github.com/repos/$repoPrefix/pulls?per_page=100&state=all&sort=created&direction=asc";
                        $lastPage = get_last_page(get_github_data($url, 1, $useKey));

                        $pulls = Pull::where("repo", $repo->id)->count();
                        $firstPage = (int)floor($pulls / 100);
                        echo "Get pull request with $lastPage page!" . PHP_EOL;
                        $total_pulls = 0;
                        for ($i = $firstPage; $i <= $lastPage; $i++) {
                            $pageUrl = $url . "&page=$i";
                            $data = json_decode(get_github_data($pageUrl, 1, $useKey));
                            if (isset($data->message))
                                throw new \Exception($data->message);

                            foreach ($data as $pull) {
                                if ($pull->state == "closed")
                                    $total_pulls += 1;
                                if (!$exist = Pull::where("pull_id", $pull->id)->first()) {
                                    Pull::create([
                                        "pull_id" => $pull->id,
                                        "author" => $pull->user->login,
                                        "status" => $pull->state,
                                        "repo" => $repo->id,
                                        "chain" => $repo->chain,
                                        "created_date" => date("Y-m-d", strtotime($pull->created_at)),
                                    ]);
                                } else {
                                    $exist->created_date = date("Y-m-d", strtotime($pull->created_at));
                                    $exist->save();
                                }
                            }
                        }
                        $repo->pull_request_closed = $total_pulls;
                    }

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
//            break;
        }


        \Log::info("End get repositories at " . now("Asia/Bangkok")->toDateTimeString());
    }
}
