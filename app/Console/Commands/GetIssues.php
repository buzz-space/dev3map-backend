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

class GetIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function shandle()
    {
        foreach (Repository::orderBy("chain", "ASC")->get() as $repo) {
            try {
                if ($repo->chain != 4) continue;
                echo "Process repo " . $repo->name . PHP_EOL;
                $prefix = $repo->github_prefix;
                $url = "https://api.github.com/repos/$prefix/issues?per_page=100&state=closed";
                $lastPage = get_last_page(get_github_data($url, "header"));
                echo "Total page: " . $lastPage . PHP_EOL;
                for ($i = 1; $i <= $lastPage; $i++) {
                    echo "Process page: $i" . PHP_EOL;
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl));
                    if (isset($data->message))
                        throw new \Exception($data->message);

//                    Log::info(print_r($data[0], true));
                    foreach ($data as $issue){
                        if (!$exist = Issue::where("issue_id", $issue->id)->first()){
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
            } catch (\Exception $exception) {
                echo $exception->getMessage() . PHP_EOL;
            }
        }
    }

    public function handle()
    {
        $chain = Chain::find($this->ask("Chain id:"));
        foreach (Repository::where("chain", $chain->id)->get() as $repo) {
            try {
                echo "Process repo " . $repo->name . " with id " . $repo->id . PHP_EOL;
                $prefix = $repo->github_prefix;
                // Contributors
                $url = "https://api.github.com/repos/$prefix/contributors?per_page=100";
                $lastPage = get_last_page(get_github_data($url, "header"));
                $contributors = [];
                echo "Total page: " . $lastPage . PHP_EOL;
                for ($i = 1; $i <= $lastPage; $i++) {
                    echo "Process page: $i" . PHP_EOL;
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl));
                    if (isset($data->message))
                        throw new \Exception($data->message);

                    $contributors += array_column((array) $data, "login");
                }

                // Fork contributor
                $infoUrl = "https://api.github.com/repos/$prefix";
                $info = json_decode(get_github_data($infoUrl));
                if ($info->fork) {
                    echo "Repo " . $repo->name . " has fork is " . $info->parent->name . PHP_EOL;
                    $cUrl = "https://api.github.com/repos/" . $info->parent->full_name . "/contributors?per_page=100";
                    $parentContributors = array_column((array)json_decode(get_github_data($cUrl)), "login");

                    $contributors = array_filter($contributors, function ($row) use ($parentContributors){
                        return !in_array($row, $parentContributors);
                    });
                }
                if (!$exist = Contributor::where("repo", $repo->id)->first()){
                    Contributor::create([
                        "repo" => $repo->id,
                        "contributors" => implode(",", $contributors),
                        "chain" => $repo->chain
                    ]);
                }
                else{
                    $exist->contributors = implode(",", $contributors);
                    $exist->save();
                }

                // Issue
                $url = "https://api.github.com/repos/$prefix/issues?per_page=100&state=closed";
                $lastPage = get_last_page(get_github_data($url, "header"));
                echo "Total page: " . $lastPage . PHP_EOL;
                for ($i = 1; $i <= $lastPage; $i++) {
                    echo "Process page: $i" . PHP_EOL;
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl));
                    if (isset($data->message))
                        throw new \Exception($data->message);

//                    Log::info(print_r($data[0], true));
                    foreach ($data as $issue){
                        if (!$exist = Issue::where("issue_id", $issue->id)->first()){
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

                $url = "https://api.github.com/repos/$prefix/pulls?per_page=100&state=closed";
                $lastPage = get_last_page(get_github_data($url, "header"));
                echo "Total page: " . $lastPage . PHP_EOL;
                for ($i = 1; $i <= $lastPage; $i++) {
                    echo "Process page: $i" . PHP_EOL;
                    $pageUrl = $url . "&page=$i";
                    $data = json_decode(get_github_data($pageUrl));
                    if (isset($data->message))
                        throw new \Exception($data->message);

                    foreach ($data as $pull){
                        if (!$exist = Pull::where("pull_id", $pull->id)->first()){
                            Pull::create([
                                "pull_id" => $pull->id,
                                "author" => $pull->user->login,
                                "status" => $pull->state,
                                "repo" => $repo->id,
                                "chain" => $repo->chain
                            ]);
                        }
                    }
                }
            } catch (\Exception $exception) {
                echo $exception->getMessage() . PHP_EOL;
            }
        }
    }
}
