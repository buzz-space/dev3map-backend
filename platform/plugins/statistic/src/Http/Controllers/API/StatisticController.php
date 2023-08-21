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
            foreach ($categories as $z){
                $query->where("categories", "like", "%$z%");
            }
        }
        $data = $query->select(
            'id',
            'name',
            'github_prefix',
            'slug',
            'symbol',
            'categories',
            'avatar',
            "subscribers",
            'website',
            "rising_star",
            "ibc_astronaut",
            "seriousness"
        )->get();
        foreach ($data as $item){
            $present = ChainInfo::where("chain", $item->id)->where("range", 0)->first();
            $other = ChainInfo::where("chain", $item->id)->where("range", "!=", 0)->get();
            foreach ($other as $range){
                $range->total_commits = $present->total_commits - $range->total_commits;
//                $range->full_time_developer = $present->full_time_developer - $range->full_time_developer;
//                $range->part_time_developer = $present->part_time_developer - $range->part_time_developer;
//                $range->total_star = $present->total_star - $range->total_star;
//                $range->total_fork = $present->total_fork - $range->total_fork;
                $range->total_repository = $present->total_repository - $range->total_repository;
                $range->total_issue_solved = $present->total_issue_solved - $range->total_issue_solved;
                $range->total_pull_merged = $present->total_pull_merged - $range->total_pull_merged;
            }
            $item->stats = $other;
            $item->github_prefix = $item->slug;
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

        if ($chain->is_repo){
            $repo = Repository::where("chain", $chain->id)->first();
            if ($repo)
                $chain->github_prefix = $repo->github_prefix;
        }
        $chain->stats = $chain->stats()->where("range", 0)->get();
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
        }
        else{
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
            $info = $chain->info()->where("range", 0)->first();

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
        }
        else{
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

    public function getCommitChart(Request $request, BaseHttpResponse $response)
    {
        if ($chain = Chain::find($request->input("chain"))){
            $data = CommitChart::where("chain", $chain->id)
                ->orderBy("year", "DESC")->orderBy("month", "DESC")->orderBy("week", "DESC")
                ->select("week", "month", "year", "total_commit", "total_additions", "total_deletions")
                ->take(62)->get()->toArray();
        }
        else {
            $data = CommitChart::groupByRaw("week, month, year")
                ->selectRaw("week, month, year, SUM(total_commit) as total_commit, SUM(total_additions) as total_additions, SUM(total_deletions) as total_deletions")
                ->orderBy("year", "DESC")->orderBy("month", "DESC")->orderBy("week", "DESC")
                ->take(62)->get()->toArray();
        }
        $data = array_reverse($data);
        return $response->setData($data);
    }

    public function getDeveloperChart(Request $request, BaseHttpResponse $response)
    {
        if ($chain = Chain::find($request->input("chain"))){
            $data = Commit::where("chain", $chain->id)
                ->selectRaw("exact_date, (total_full_time + total_part_time) as active_developer")
                ->orderBy("exact_date", "DESC")->limit(500)->get();
        }
        else {
            $data = Commit::groupBy("exact_date")
                ->selectRaw("exact_date, (SUM(total_full_time) + SUM(total_part_time)) as active_developer")
                ->orderBy("exact_date", "DESC")->limit(500)->get();
        }

        $data = array_reverse($data->toArray());

        return $response->setData($data);
    }

    public function getCategories(Request $request, BaseHttpResponse $response)
    {
        $data = Chain::whereNotNull("categories")->pluck("categories")->toArray();
        $data = array_values(array_unique(explode(",", implode(",", $data))));
        sort($data);
        $additionalData = $request->has("with_data");
        $z = [];
        foreach ($data as $item){
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
        foreach ($data as $chain){
            $info = $chain->info()->where("range", 0)->first();
            $chain->total_commit = $info->total_commits ?? 0;
            $chain->total_pull_merged = $info->total_pull_merged ?? 0;
            $chain->total_developer = ($info->full_time_developer ?? 0) + ($info->part_time_developer ?? 0);
            $chain->total_issue = $info->total_issue_solved ?? 0;
            $chain->total_star = $info->total_star ?? 0;
            $chain->total_fork = $info->total_fork ?? 0;
            $chain->total_pull_request = $info->total_pull_request ?? 0;
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

    public function getChainRepository($chain_id, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($chain_id))
            return $response->setError()->setMessage("Chain not found!");

        $repos = Repository::where("chain", $chain->id)
            ->selectRaw("id, name, github_prefix, description, contributors, total_star, total_commit")
            ->orderBy("total_commit", "DESC")->orderBy("total_star", "DESC")->orderBy("contributors", "DESC")
            ->get();

        return $response->setData($repos);
    }

    public function getTopDeveloper($chain_id, BaseHttpResponse $response)
    {
        if (!$chain = Chain::find($chain_id))
            return $response->setError()->setMessage("Chain not found!");

        $repos = Repository::where("chain", $chain->id)->select("name", "total_contributor")->get()->toArray();
        $contributors = unique_name(array_column($repos, "total_contributor"));

        $pullDevelopers = Pull::where("chain", $chain->id)->whereIn("author", $contributors)
            ->select("author", "status")->get()->toArray();

        $calculate = Pull::where("chain", $chain->id)->whereIn("author", $contributors)
            ->groupBy("author")
            ->selectRaw("author, COUNT(*) as total")->orderBy("total", "DESC")->get();

        $commits = explode(",", implode(",", Commit::where("chain", $chain->id)->pluck("author_list")->toArray()));
        $commits = array_count_values($commits);

        foreach ($calculate as $item){
            $author = $item->author;
            $item->closed = count(array_filter($pullDevelopers, function ($row) use ($author){
                return $row["author"] == $author && $row["status"] == "closed";
            }));
            $item->repos = array_column(array_filter($repos, function ($row) use ($author){
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
