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
use Botble\Statistic\Models\Developer;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $data = $query->selectRaw("id, name, slug as github_prefix, symbol, avatar")->get();
        foreach ($data as $item) {
            $stats = $item->stats()->whereNotIn("range", ["before_7_days", "before_30_days", "24_hours"])->get();
            $before['7_days'] = $item->stats()->where("range", "before_7_days")->first();
            $before['30_days'] = $item->stats()->where("range", "before_30_days")->first();
            foreach ($stats as $stat) {
                if ($stat->range == "all") continue;
                $stat->commit_percent = number_format(check_percent($stat->total_commits / ($before[$stat->range]->total_commits > 0 ? $before[$stat->range]->total_commits : 1) * 100), 2);
                $stat->developer_percent = number_format(check_percent(($stat->total_developer) / ($before[$stat->range]->total_developer > 0 ? $before[$stat->range]->total_developer : 1) * 100), 2);
                $stat->repository_percent = number_format(check_percent($stat->total_repository / ($before[$stat->range]->total_repository > 0 ? $before[$stat->range]->total_repository : 1) * 100), 2);
//                $stat->star_percent = number_format(check_percent($stat->total_star / ($before[$stat->range]->total_star > 0 ? $before[$stat->range]->total_star : 1) * 100), 2);
//                $stat->fork_percent = number_format(check_percent($stat->total_fork / ($before[$stat->range]->total_fork > 0 ? $before[$stat->range]->total_fork : 1) * 100), 2);
                $stat->issue_percent = number_format(check_percent($stat->total_issue_solved / ($before[$stat->range]->total_issue_solved > 0 ? $before[$stat->range]->total_issue_solved : 1) * 100), 2);
                $stat->pull_percent = number_format(check_percent($stat->total_pull_merged / ($before[$stat->range]->total_pull_merged > 0 ? $before[$stat->range]->total_pull_merged : 1) * 100), 2);
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
            "rising_star",
            "ibc_astronaut",
            "seriousness",
            "is_repo"
        )->first())
            return $response->setError()->setMessage("Chain not found!");

        if ($chain->is_repo) {
            $repo = Repository::where("chain", $chain->id)->first();
            if ($repo)
                $chain->github_prefix = $repo->github_prefix;
        }
        $chain->stats = $chain->stats()->where("range", "all")->first();
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
                "total_commit" => Commit::sum("total_commit"),
                "total_issue" => Issue::count(),
                "total_pull_request" => Pull::count(),
                "total_star" => Repository::sum("total_star"),
                "total_fork" => Repository::sum("total_fork")
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
                "total_commit" => Commit::where("chain", $chain->id)->sum("total_commit"),
                "total_issue" => Issue::where("chain", $chain->id)->count(),
                "total_pull_request" => Pull::where("chain", $chain->id)->count(),
                "total_star" => Repository::where("chain", $chain->id)->sum("total_star"),
                "total_fork" => Repository::where("chain", $chain->id)->sum("total_fork"),
                "total_developer" => $info->full_time_developer + $info->part_time_developer,
                "issue_performance" => number_format($info->issue_performance, 2),
                "community_attribute" => number_format($info->community_attribute, 2),
            ];
        } else {
            $info = ChainInfo::where("range", "24_hours")
                ->select("full_time_developer", "part_time_developer")->get()->toArray();

            $data = [
                "total_commit" => Commit::sum("total_commit"),
                "total_issue" => Issue::count(),
                "total_pull_request" => Pull::count(),
                "total_star" => Repository::sum("total_star"),
                "total_fork" => Repository::sum("total_fork"),
                "total_developer" => array_sum(array_column($info, "full_time_developer")) + array_sum(array_column($info, "part_time_developer")),
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
        $data = Chain::orderBy($type, "DESC")->take(100)->get();
        $total_chain = Chain::count();
        foreach ($data as $chain) {
            $now = $chain->info()->where("range", "all")->first();
            $info = $chain->info()->where("range", "7_days")->first();
            $chain->total_commit = $info->total_commits;
            $chain->total_pull_merged = $info->total_pull_merged;
            $chain->total_developer = ($info->full_time_developer) + ($info->part_time_developer);
            $chain->total_issue = $info->total_issue_solved;
            $chain->total_star = $info->total_star;
            $chain->total_fork = $info->total_fork;
            $chain->total_pull_request = $info->total_pull_request;
            $chain->commit_score = 101 - $chain->commit_rank;
            $chain->pulls_score = 101 - $chain->pull_rank;
            $chain->dev_score = 101 - $chain->dev_rank;
            $chain->issue_score = 101 - $chain->issue_rank;
            $chain->star_score = 101 - $chain->star_rank;
            $chain->fork_score = 101 - $chain->fork_rank;
            $chain->pr_score = 101 - $chain->pr_rank;

            $chain->total_chain = $total_chain;
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
            "is_repo" => $request->has("is_repo")
        ]);

        return $response->setMessage("Created " . $chain->name);
    }
}
