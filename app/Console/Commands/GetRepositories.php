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
        $useKey = 1;
        $chains = Chain::orderBy("id", "ASC");
        if ($toChain > 0)
            $chains->where("id", "<=", $toChain);
        $chains = $chains->get();

        $count = 0;
        foreach ($chains as $i => $chain) {
            $chain->subscribers = 0;
            if ($chain->id < $chainId) continue;
            echo "Chain " . $chain->name . PHP_EOL;

            if (!$chain->is_repo) {
                $chainUrl = "https://api.github.com/orgs/" . $chain->github_prefix;
                $chainInfo = json_decode(get_github_data($chainUrl, 1, $useKey));

                $chain->avatar = $chainInfo->avatar_url ?? null;
                $chain->website = $chainInfo->blog ?? "";
                // Get all repository from chain (test aura-nw)
                $prefix = $chain->github_prefix;
                $url = "https://api.github.com/orgs/$prefix/repos?per_page=100";
                $lastPage = get_last_page(get_github_data($url, 0, $useKey));
                $repository = Repository::where("chain", $chain->id)->pluck("github_prefix", "name")->toArray();
                $repository = array_merge($repository, array_column((array)json_decode(get_github_data($url . "&page=$lastPage", 1, $useKey)), "full_name", "name"));
            } else
                $repository = [$chain->name => $chain->github_prefix];

            echo "With " . count($repository) . PHP_EOL;
            foreach ($repository as $name => $repoPrefix) {
                $count++;
                $useKey = ((floor($count / 100) % 2 != 0) ? 2 : 1);

                if (strpos($repoPrefix, "chromium") !== false || strpos($repoPrefix, "linux") !== false) continue;
                $repoUrl = "https://api.github.com/repos/$repoPrefix";
                echo "$count-$useKey. Repo " . $repoUrl . " of chain " . $chain->name . PHP_EOL;
                $repoInfo = json_decode(get_github_data($repoUrl, 1, $useKey));
                if (isset($repoInfo->message)) {
                    if (strpos($repoInfo->message, "Not Found") !== false) {
                        \Log::info($repoPrefix . " not found!");
                        continue;
                    } elseif (strpos($repoInfo->message, "Moved") !== false) {
                        $repoInfo = json_decode(get_github_data($repoInfo->url, 1, $useKey));
                        \Log::info($repoPrefix . " is moved to " . $repoInfo->full_name);
                        $name = $repoInfo->name;
                        $repoPrefix = $repoInfo->full_name;
                    } else {
                        \Log::info($repoPrefix . " with error " . json_encode($repoInfo));
                        continue;
                    }
                }
                if (!$repo = Repository::where("github_prefix", $repoPrefix)->first()) {
                    $repo = new Repository();
                    $repo->name = $name;
                    $repo->github_prefix = $repoPrefix;
                    $repo->chain = $chain->id;
                    $repo->save();
                }
                $repo->total_star = $repoInfo->stargazers_count;
                $repo->total_fork = $repoInfo->forks_count;
                $repo->subscribers = $repoInfo->subscribers_count;
                $repo->description = $repoInfo->description;
                $repo->created_date = date("Y-m-d H:i:s", strtotime($repoInfo->created_at));
                $repo->is_fork = $repoInfo->fork;
                $repo->save();

                echo "Repo id " . $repo->id . ":" . $chain->id . PHP_EOL;
                if ($chain->id == $chainId && $repo->id < $repoId) continue;

                // Contributors
                $url = "https://api.github.com/repos/$repoPrefix/contributors?per_page=100";
                $lastPage = get_last_page(get_github_data($url, 0, $useKey));
                $contributors = [];
                echo "Get contributors!" . PHP_EOL;
                for ($i = 1; $i <= $lastPage; $i++) {
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl, 1, $useKey));

                    $contributors += array_column((array)$data, "login");
                }
                $contributors = array_filter($contributors, function ($row) {
                    return strpos($row, "[bot]") === false;
                });

                // Fork contributor
                if ($repoInfo->fork) {
                    $cUrl = "https://api.github.com/repos/" . $repoInfo->parent->full_name . "/contributors?per_page=100";
                    $parentContributors = array_column((array)json_decode(get_github_data($cUrl, 1, $useKey)), "login");

                    $contributors = array_filter($contributors, function ($row) use ($parentContributors) {
                        return !in_array($row, $parentContributors);
                    });
                }
                $repo->total_contributor = implode(",", $contributors);
                $repo->contributors = count($contributors);

                // Issue
                $url = "https://api.github.com/repos/$repoPrefix/issues?per_page=100&state=closed";
                if ($lastIssue = Issue::where("repo", $repo->id)->orderBy("open_date", "DESC")->first())
                    $url .= ("&since=" . date(DATE_ATOM, strtotime($lastIssue->open_date)));
                $lastPage = get_last_page(get_github_data($url, 0, $useKey));
                echo "Get issue solved with $lastPage page!" . PHP_EOL;
                for ($i = 1; $i <= $lastPage; $i++) {
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl, 1, $useKey));

                    foreach ($data as $issue) {
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

                // Pull
                $url = "https://api.github.com/repos/$repoPrefix/pulls?per_page=100&state=all&sort=created&direction=asc";
                $lastPage = get_last_page(get_github_data($url, 1, $useKey));

                $pulls = Pull::where("repo", $repo->id)->count();
                $firstPage = (int)floor($pulls / 100);
                echo "Get pull request with $lastPage page!" . PHP_EOL;
                for ($i = $firstPage; $i <= $lastPage; $i++) {
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl, 1, $useKey));
                    if (isset($data->message))
                        throw new \Exception($data->message);

                    foreach ($data as $pull) {
                        if ($pull->state == "closed")
                            if (!$exist = Pull::where("pull_id", $pull->id)->first()) {
                                Pull::create([
                                    "pull_id" => $pull->id,
                                    "author" => $pull->user->login,
                                    "status" => $pull->state,
                                    "repo" => $repo->id,
                                    "chain" => $repo->chain,
                                    "created_date" => date("Y-m-d", strtotime($pull->created_at)),
                                ]);
                            }
                    }
                }

                $chain->subscribers += $repoInfo->subscribers_count;
            }

            $chain->last_updated = now();
            $chain->save();
        }
        \Log::info("End get repositories at " . now("Asia/Bangkok")->toDateTimeString());
    }
}
