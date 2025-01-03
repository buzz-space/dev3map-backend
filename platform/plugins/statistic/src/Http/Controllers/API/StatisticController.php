<?php

namespace Botble\Statistic\Http\Controllers\API;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Statistic\Jobs\GetInfoChain;
use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainInfo;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\DeveloperStatistic;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StatisticController extends BaseController
{
    public function chainList(Request $request, BaseHttpResponse $response)
    {
        $query = Chain::query();
        $categories = explode(',', $request->input("categories", ""));
        if (!empty($categories)) {
            foreach ($categories as $z) {
                $query->where("categories", "like", "%$z%");
            }
        }
        $data = $query->selectRaw("id, name, slug as github_prefix, symbol, avatar, total_contributor")->get();
        foreach ($data as $item) {
            $stats = $item->stats()->whereIn("range", ["7_days", "30_days", "all"])->get();
            $before['7_days'] = $item->stats()->where("range", "before_7_days")->first();
            $before['30_days'] = $item->stats()->where("range", "before_30_days")->first();
            $before['all'] = $item->stats()->where("range", "all")->first();
            foreach ($stats as $stat) {
                if ($stat->range == "all") {
                    $stat->full_time_developer = $item->total_contributor;
                    $stat->part_time_developer = 0;
                }
                else{
                    $star = $before['all']->total_star - $stat->total_star; $starLast = (($minus = $stat->total_star - $before[$stat->range]->total_star) > 0) ? $minus : 1;
                    $fork = $before['all']->total_fork - $stat->total_fork; $forkLast = (($minus = $stat->total_fork - $before[$stat->range]->total_fork) > 0) ? $minus : 1;
                    $stat->commit_percent = number_format(check_percent($stat->total_commits / ($before[$stat->range]->total_commits > 0 ? $before[$stat->range]->total_commits : 1) * 100), 2);
                    $stat->developer_percent = number_format(check_percent(($stat->total_developer) / ($before[$stat->range]->total_developer > 0 ? $before[$stat->range]->total_developer : 1) * 100), 2);
                    $stat->repository_percent = number_format(check_percent($stat->total_repository / ($before[$stat->range]->total_repository > 0 ? $before[$stat->range]->total_repository : 1) * 100), 2);
                    $stat->issue_percent = number_format(check_percent($stat->total_issue_solved / ($before[$stat->range]->total_issue_solved > 0 ? $before[$stat->range]->total_issue_solved : 1) * 100), 2);
                    $stat->pull_percent = number_format(check_percent($stat->total_pull_merged / ($before[$stat->range]->total_pull_merged > 0 ? $before[$stat->range]->total_pull_merged : 1) * 100), 2);
                    $stat->star_percent = number_format(check_percent($star / $starLast * 100), 2);
                    $stat->fork_percent = number_format(check_percent($fork / $forkLast * 100), 2);
                    $stat->total_star = $star;
                    $stat->total_fork = $fork;
                }

            }

            $item->stats = $stats;
        }
        return $response->setData($data);
    }

    public function chainInfo($prefix, BaseHttpResponse $response)
    {
        if (!$chain = Chain::where("slug", $prefix)->select(
            'id',
            'name',
            'github_prefix',
            'categories',
            'avatar',
            "subscribers",
            'website',
            "description",
            "refer_ici",
            "is_repo"
        )->with("resources")->first())
            return $response->setError()->setMessage("Chain not found!");

        if ($chain->is_repo) {
            $repo = Repository::where("chain", $chain->id)->first();
            if ($repo)
                $chain->github_prefix = $repo->github_prefix;
        }
        $chain->stats = $chain->stats()->whereIn("range", ["all", "7_days", "30_days"])->get();
        return $response->setData($chain);
    }

    public function commitInfo(Request $request, BaseHttpResponse $response)
    {
        if ($chain = Chain::find($request->input("chain"))) {

            $data = [
                "total_commit" => Commit::where("chain", $chain->id)->sum("total_commit"),
                "total_issue" => Issue::where("chain", $chain->id)->count(),
                "total_pull_request" => Pull::where("chain", $chain->id)->count(),
                "total_star" => Repository::where("chain", $chain->id)->sum("total_star"),
                "total_fork" => Repository::where("chain", $chain->id)->sum("total_fork")
            ];
        } else {
            $data = [
                "total_commit" => setting("total_commit", 0),
                "total_issue" => setting("total_issue", 0),
                "total_pull_request" => setting("total_pull", 0),
                "total_star" => setting("total_star", 0),
                "total_fork" => setting("total_fork", 0),
//                "total_commit" => Commit::sum("total_commit"),
//                "total_issue" => Issue::count(),
//                "total_pull_request" => Pull::count(),
//                "total_star" => Repository::sum("total_star"),
//                "total_fork" => Repository::sum("total_fork"),
//
            ];
        }
        return $response->setData($data);
    }

    public function summaryInfo(Request $request, BaseHttpResponse $response)
    {
        if ($chain = Chain::find($request->input("chain"))) {
            // Devs
            $info = $chain->info()->where("range", "all")->first();

            $data = [
                "total_commit" => $info->total_commits,
                "total_issue" => $info->total_issue_solved,
                "total_pull_request" => $info->total_pull_request,
                "total_star" => $info->total_star,
                "total_fork" => $info->total_fork,
                "total_developer" => $info->full_time_developer + $info->part_time_developer,
                "issue_performance" => number_format($info->issue_performance, 2),
                "community_attribute" => number_format($info->community_attribute, 2),
            ];
        } else {
            $info = ChainInfo::where("range", "all")->get()->toArray();
            $info7Days = ChainInfo::where("range", "7_days")->get()->toArray();

            $data = [
                "total_commit" => array_sum(array_column($info, "total_commits")),
                "total_issue" => array_sum(array_column($info, "total_issue_solved")),
                "total_pull_request" => array_sum(array_column($info, "total_pull_request")),
                "total_star" => array_sum(array_column($info, "total_star")),
                "total_fork" => array_sum(array_column($info, "total_fork")),
                "total_developer" => array_sum(array_column($info7Days, "full_time_developer")) + array_sum(array_column($info7Days, "part_time_developer")),
                "issue_performance" => setting("issue_performance", 0),
                "community_attribute" => setting("community_attribute", 0),
            ];
        }
        return $response->setData($data);
    }

    // unused
    public function getCommitChart(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            'filter' => "nullable|in:7,30",
        ]);

        if ($validator->fails())
            return $response->setError()->setMessage(processValidators($validator->errors()->toArray()));

        if ($chain = Chain::find($request->input("chain"))) {
            $data = CommitChart::where("chain", $chain->id)
                ->orderBy("year", "DESC")->orderBy("month", "DESC")->orderBy("week", "DESC")
                ->select("week", "month", "year", "total_commit", "total_additions", "total_deletions");
        } else {
            $data = CommitChart::groupByRaw("week, month, year")
                ->selectRaw("week, month, year, SUM(total_commit) as total_commit, SUM(total_additions) as total_additions, SUM(total_deletions) as total_deletions")
                ->orderBy("year", "DESC")->orderBy("month", "DESC")->orderBy("week", "DESC");
        }

        $data = array_reverse($data->take(62)->get()->toArray());
        return $response->setData($data);
    }

    public function getDeveloperChart(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            'filter' => "nullable|in:7,30",
        ]);

        if ($validator->fails())
            return $response->setError()->setMessage(processValidators($validator->errors()->toArray()));

        if ($chain = Chain::find($request->input("chain"))) {
            $data = Commit::where("chain", $chain->id);
        } else
            $data = Commit::query();

        $data->groupBy("exact_date")
            ->selectRaw("exact_date, (SUM(total_full_time) + SUM(total_part_time)) as active_developer, SUM(total_commit) as total_commit,
             SUM(additions) as additions, SUM(deletions) as deletions")
            ->orderBy("exact_date", "DESC");

        $filter = $request->input("filter", false);
        if ($filter)
            $data->where("exact_date", ">=", now()->addDays(-1 * $filter));

        $data = array_reverse($data->limit(500)->get()->toArray());

        return $response->setData($data);
    }

    public function getDeveloperChartBackup(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            'filter' => "nullable|in:7,30",
        ]);

        if ($validator->fails())
            return $response->setError()->setMessage(processValidators($validator->errors()->toArray()));

        if ($chain = Chain::find($request->input("chain"))) {
            $data = Commit::where("chain", $chain->id);
        } else
            $data = Commit::query();

        $data->groupBy("exact_date")
            ->selectRaw("exact_date, (SUM(total_full_time) + SUM(total_part_time)) as active_developer, SUM(total_commit) as total_commit,
             SUM(additions) as additions, SUM(deletions) as deletions")
            ->orderBy("exact_date", "ASC");

//        $filter = $request->input("filter", false);
//        if ($filter)
//            $data->where("exact_date", ">=", now()->addDays(-1 * $filter));
        $data->where("exact_date", ">=", now()->startOfYear());

//        $data = array_reverse($data->limit(500)->get()->toArray());
        $data = $data->get();

        if ($chain = Chain::find($request->input("chain"))) {
            $start = Commit::where("chain", $chain->id);
        } else
            $start = Commit::query();
        $total = $start->groupBy("exact_date")
            ->selectRaw("exact_date, SUM(total_commit) as total_commit")
            ->where("exact_date", "<", now()->startOfYear())->get()->toArray();
        $total = array_sum(array_column($total, "total_commit"));
        foreach ($data as $item){
            $total += $item->total_commit;
            $item->total_commit = $total;
        }

        return $response->setData($data);
    }

    public function getCategories(Request $request, BaseHttpResponse $response)
    {
        $data = Chain::whereNotNull("categories")->pluck("categories")->toArray();
        $data = array_values(array_unique(explode(",", implode(",", $data))));
        sort($data);
        $additionalData = $request->has("with_data");
        $z = [];
        foreach ($data as $item) {
            $chains = Chain::where("categories", "like", "%$item%")
                ->selectRaw("id, name, slug as github_prefix, avatar")->get();
            $row = [
                'name' => $item,
                'total' => count($chains)
            ];
            if ($additionalData)
                $row["chain"] = $chains;
            $z[] = $row;
        }
        return $response->setData($z);
    }

    public function ranking(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            'type' => "required|in:rising_star,ibc_astronaut,seriousness"
        ]);

        if ($validator->fails())
            return $response->setError()->setMessage(processValidators($validator->errors()->toArray()));

        $type = $request->input("type");
        $data = ChainInfo::where("range", "7_days")->orderBy($type, "DESC")->take(100)->get();
        $total_chain = Chain::count();
        foreach ($data as $info) {
            $lastInfo = ChainInfo::where("range", "before_7_days")->where("chain", $info->chain)->first();
            $info->total_commit = $info->total_commits;
            $info->commit_score = 101 - $info->commit_rank;
            $info->pulls_score = 101 - $info->pull_rank;
            $info->dev_score = 101 - $info->dev_rank;
            $info->issue_score = 101 - $info->issue_rank;
            $info->star_score = 101 - $info->star_rank;
            $info->fork_score = 101 - $info->fork_rank;
            $info->pr_score = 101 - $info->pr_rank;
            $info->total_fork -= $lastInfo->total_fork;
            $info->total_issue = $info->total_issue_solved;
            $info->total_developer = ($info->full_time_developer) + ($info->part_time_developer);

            $info->name = $info->chain_info ? $info->chain_info->name : "";
            $info->avatar = $info->chain_info ? $info->chain_info->avatar : "";
            $info->github_prefix = $info->chain_info ? $info->chain_info->slug : "";
            $info->total_chain = $total_chain;
        }
        return $response->setData($data);
    }

    public function getChainRepository($chain_id, Request $request, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($chain_id))
            return $response->setError()->setMessage("Chain not found!");

        $repos = Repository::where("chain", $chain->id)
            ->selectRaw("id, name, github_prefix, description, contributors, total_star, total_commit, is_fork")
            ->orderBy("total_commit", "DESC")->orderBy("total_star", "DESC")->orderBy("contributors", "DESC");

        if ($request->input("hide_fork", false))
            $repos->where("is_fork", false);

        return $response->setData($repos->get());
    }

    public function getTopDeveloper($chain_id, Request $request, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($chain_id))
            return $response->setError()->setMessage("Chain not found!");

        $repos = Repository::where("chain", $chain->id)->select("name", "total_contributor")->get()->toArray();
        $contributors = unique_name(array_column($repos, "total_contributor"));
        $found = array_search("dependabot[bot]", $contributors);
        if ($found)
            unset($contributors[$found]);

        $pullDevelopers = Pull::where("chain", $chain->id)->whereIn("author", $contributors)
            ->select("author", "status")->get()->toArray();

        $calculate = Pull::where("chain", $chain->id)->whereIn("author", $contributors)
            ->groupBy("author")
            ->selectRaw("author, COUNT(*) as total")->orderBy("total", $request->input("sort", "DESC"))->get();

        $commits = explode(",", implode(",", Commit::where("chain", $chain->id)->pluck("author_list")->toArray()));
        $commits = array_count_values($commits);

        foreach ($calculate as $item) {
            $author = $item->author;
            $item->closed = count(array_filter($pullDevelopers, function ($row) use ($author) {
                return $row["author"] == $author && $row["status"] == "closed";
            }));
            $item->repos = array_column(array_filter($repos, function ($row) use ($author) {
                return strpos($row["total_contributor"], $author);
            }), "name");
            $item->commits = isset($commits[$author]) ? $commits[$author] : 0;
        }

        return $response->setData($calculate);
    }

    public function getPerformance($chain_id, Request $request, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($chain_id))
            return $response->setError()->setMessage("Chain not found!");

        $ranges = [
            'all',
            '7_days',
            '30_days'
        ];

        $info = ChainInfo::where('chain', $chain_id)->whereIn('range', $ranges)->get();

        return $response->setData($info);
    }

    public function addChain(Request $request, BaseHttpResponse $response)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "github_prefix" => "required|unique:chains,github_prefix",
            "categories" => "required",
        ]);

        if ($validator->fails())
            return $response->setError()->setMessage(processValidators($validator->errors()->toArray()));

        $chain = Chain::create([
            "name" => $request->input("name"),
            "github_prefix" => $request->input("github_prefix"),
            "categories" => $request->input("categories"),
            "is_repo" => $request->has("is_repo"),
            "symbol" => $request->input("symbol")
        ]);

        return $response->setMessage("Created " . $chain->name);
    }

    public function getContributorInfo($login, BaseHttpResponse $response)
    {
        if (!$dev = Contributor::where("login", $login)->first())
            return $response->setError()->setMessage("Developer not found!");

        return $response->setData($dev);
    }

    public function getContributorActivity($login, Request $request, BaseHttpResponse $response)
    {
        if (!$dev = Contributor::where("login", $login)->first())
            return $response->setError()->setMessage("Developer not found!");

        $month = $request->input("month", now()->month);
        $year = $request->input("year", now()->year);

        $date = Carbon::create($year, $month, 1);
        $commits = Commit::where([
            ["exact_date", ">=", $date->toDateString()],
            ["exact_date", "<=", (clone $date)->endOfMonth()->toDateString()],
            ["author_list", "like", "%$login%"]
        ])->groupBy("exact_date")->selectRaw("exact_date, GROUP_CONCAT(author_list SEPARATOR ',') as author")
            ->orderBy("exact_date", "ASC")->get();
        $lstCommit = [];
        foreach ($commits as $item){
            $listContributor = array_filter(explode(",", $item->author));
            $values = array_count_values($listContributor);
            $item->total = isset($values[$login]) ? $values[$login] : 0;
            unset($item->author);

            $lstCommit[$item->exact_date] = $item->total;
        }
        $lstIssue = Issue::where([
            ["open_date", ">=", $date->toDateString()],
            ["open_date", "<=", (clone $date)->endOfMonth()->toDateString()],
            ["creator", "like", "%$login%"]
        ])->groupBy("open_date")->selectRaw("open_date as exact_date, COUNT(*) as creator")
            ->orderBy("exact_date", "ASC")->pluck("creator", "exact_date")->toArray();

        $lstPull = Pull::where([
            ["created_date", ">=", $date->toDateString()],
            ["created_date", "<=", (clone $date)->endOfMonth()->toDateString()],
            ["author", "like", "%$login%"]
        ])->groupBy("created_date")->selectRaw("created_date as exact_date, COUNT(*) as creator")
            ->orderBy("exact_date", "ASC")->pluck("creator", "exact_date")->toArray();

        $res = [];
        for ($i = 0; $i < $date->daysInMonth; $i++){
            $s = (clone $date)->addDays($i)->toDateTimeString();
            $res[] = [
                "commit" => isset($lstCommit[$s]) ? $lstCommit[$s] : 0,
                "issue" => isset($lstIssue[$s]) ? $lstIssue[$s] : 0,
                "pull" => isset($lstPull[$s]) ? $lstPull[$s] : 0,
                'total' => (isset($lstCommit[$s]) ? $lstCommit[$s] : 0) + (isset($lstIssue[$s]) ? $lstIssue[$s] : 0)
                    + (isset($lstPull[$s]) ? $lstPull[$s] : 0)
            ] ;
        }

        return $response->setData($res);
    }

    public function getDeveloperContribution($login, Request $request, BaseHttpResponse $response)
    {
        if (!$dev = Contributor::where("login", $login)->first())
            return $response->setError()->setMessage("Developer not found!");

        $chains = Commit::where("author_list", "like", "%$login%")
            ->groupBy("chain")
            ->selectRaw("chain, GROUP_CONCAT(author_list SEPARATOR ',') as author")
            ->get();

        $issue = Issue::where("creator", "like", "%$login%")->groupBy("chain")
            ->selectRaw("chain, COUNT(*) as creator")->pluck("creator", "chain")->toArray();

        $pull = Pull::where("author", "like", "%$login%")->groupBy("chain")
            ->selectRaw("chain, COUNT(*) as creator")->pluck("creator", "chain")->toArray();

        $totalContribution = 0;
        foreach ($chains as $chain){
            $selectedChain = Chain::find($chain->chain);
            $chain->name = $selectedChain->name;
            $chain->avatar = $selectedChain->avatar;
            $chain->symbol = $selectedChain->symbol;
            $chain->github_prefix = $selectedChain->github_prefix;

            $listContributor = array_filter(explode(",", $chain->author));
            $values = array_count_values($listContributor);
            $chain->developer_commit = (isset($values[$login]) ? $values[$login] : 0) + (isset($issue[$chain->chain]) ? $issue[$chain->chain] : 0)
                + (isset($pull[$chain->chain]) ? $pull[$chain->chain] : 0);
//            $chain->total_commit = $selectedChain->repositories()->sum("total_commit") + $selectedChain->repositories()->sum("total_issue_solved") + $selectedChain->repositories()->sum("pull_request_closed");
            unset($chain->author);
            $totalContribution += $chain->developer_commit;
        }

        foreach ($chains as $chain){
            $chain->percent = round($chain->developer_commit / $totalContribution * 100, 2);
        }

        $chains = $chains->toArray();
        if ($request->has("sort")){
            $sort = $request->input("sort");
            if ($sort == "ASC"){
                usort($chains, function ($a, $b) {
                    return $a["percent"] - $b["percent"];
                });
            }
            else{
                usort($chains, function ($a, $b) {
                    return $b["percent"] - $a["percent"];
                });
            }
        }

        return $response->setData($chains);
    }

    public function getContributorRepositories($login, Request $request, BaseHttpResponse $response)
    {
        if (!$dev = Contributor::where("login", $login)->first())
            return $response->setError()->setMessage("Developer not found!");

        $repositories = Repository::whereIn("id", explode(",", $dev->repo))
            ->select("id", "name", "description", "github_prefix")->get();

        foreach ($repositories as $repository){
            $authors = Commit::where("repo", $repository->id)->where("author_list", "like", "%$login%")->pluck("author_list")->toArray();
            $listContributor = array_filter(explode(",", implode(",", $authors)));
            $values = array_count_values($listContributor);
            $totalCommit = isset($values[$login]) ? $values[$login] : 0;
            $issue = Issue::where("creator", "like", "%$login%")->where("repo", $repository->id)->count();
            $pull = Pull::where("author", "like", "%$login%")->where("repo", $repository->id)->count();
            $repository->total_commit = $totalCommit;
            $repository->total_issue = $issue;
            $repository->total_pull = $pull;
            $repository->total = $totalCommit + $issue + $pull;
        }

        $repositories = $repositories->toArray();
        if ($request->has("sort")){
            $sort = $request->input("sort");
            if ($sort == "ASC"){
                usort($repositories, function ($a, $b) {
                    return $a["total"] - $b["total"];
                });
            }
            else{
                usort($repositories, function ($a, $b) {
                    return $b["total"] - $a["total"];
                });
            }
        }

        return $response->setData($repositories);
    }

    public function getContributorStatistic($login, BaseHttpResponse $response)
    {
        if (!$dev = Contributor::where("login", $login)->first())
            return $response->setError()->setMessage("Developer not found!");

        return $response->setData($dev->statistic);
    }
}
