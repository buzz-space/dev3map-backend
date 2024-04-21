<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainInfo;
use Botble\Statistic\Models\Commit;
use Botble\Statistic\Models\CommitChart;
use Botble\Statistic\Models\CommitSHA;
use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Issue;
use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;

//use Carbon\Carbon;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetCommits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:commits {from_date} {from_id} {to_id}';

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
        $from = $this->argument("from_date") ?? now()->addDays(-7)->toDateString();
        $chainId = $this->argument("from_id") ?? 0;
        $start = now();
        $env = env("APP_ENV", "local");
        echo "Begin: " . $start->toDateTimeString() . PHP_EOL;
        $count = 0;
        foreach (Chain::orderBy("id", "ASC")->get() as $chain) {
            if ($chain->id < $chainId) continue;
            $repositories = Repository::where("chain", $chain->id)->orderBy("id", "ASC")->get();
            if ($env == "local") echo "Chain " . $chain->name . " with " . count($repositories) . PHP_EOL;
            try {
                foreach ($repositories as $j => $repository) {
                    $count++;
                    $useKey = ((floor($count / 100) % 2 != 0) ? 2 : 1);
                    echo ($j + 1) . ": Repo " . $repository->name . PHP_EOL;
                    /**
                     * Get commits
                     */
                    if ($env == "local") echo ($j + 1) . " (" . $chain->id . "): " . $repository->id . "-" . $repository->name . PHP_EOL;
                    $last = "2020-01-01";
                    $contributors = unique_name(explode(",", $repository->total_contributor));
                    $prefix = $repository->github_prefix;
                    if ($lastCommit = Commit::where("repo", $repository->id)->orderBy("exact_date", "DESC")->first())
                        $last = $from;
                    else {
                        if ($repository->id <= 6220) {
                            if ($env == "local") echo "Repository has no commit!" . PHP_EOL;
                            continue;
                        }
                    }
                    $until = now()->toDateTimeString();
                    $urlBranch = "https://api.github.com/repos/$prefix/branches?protected=true";
                    $branches = json_decode(get_github_data($urlBranch, 1, $useKey));
                    if (isset($branches->message)) {
                        send_telegram_message("Repository " . $repository->name . ": " . $branches->message);
                        continue;
                    }
                    if (empty($branches))
                        $branches[] = (object)["name" => ""];
                    foreach ($branches as $branch) {
                        $url = "https://api.github.com/repos/$prefix/commits?per_page=100";
                        if ($branch->name)
                            $url .= "&sha=" . $branch->name;
                        $url .= "&since=" . date(DATE_ATOM, strtotime($last));
                        $url .= "&until=" . date(DATE_ATOM, strtotime($until));
                        $dataFirstPage = get_github_data($url, 0);
                        if (isset($dataFirstPage->message)) {
                            send_telegram_message($repository->github_prefix . " with error " . json_encode($dataFirstPage));
                            continue;
                        }
                        $lastPage = get_last_page(get_github_data($url, 0));
                        if ($env == "local") echo "Total page at " . $branch->name . " : " . $lastPage . PHP_EOL;
                        for ($i = 1; $i <= $lastPage; $i++) {
                            if ($env == "local") echo "Process page $i..." . PHP_EOL;
                            $commitUrl = $url . "&page=$i";
                            $data = json_decode(get_github_data($commitUrl, 1, $useKey));
                            $date = null;
                            $save = null;
                            $sha = [];
                            foreach ($data as $z => $commit) {
                                if (!isset($commit->commit)) continue;
                                if ($commit->author)
                                    $author = $commit->author->login ?? "";
                                else
                                    $author = $commit->commit->author->name;
                                if ($repository->is_fork) {
                                    if (!in_array($author, $contributors))
                                        continue;
                                }
                                $commitDate = date("Y-m-d", strtotime($commit->commit->author->date));
                                if ($date != $commitDate) {
                                    if ($save) {
                                        $save->author_list = implode(",", $save->author_list);
                                        $save->save();

                                        // save sha
                                        $exists = CommitSHA::where("commit_id", $save->id)->pluck("sha")->toArray();
                                        $sha = array_filter($sha, function ($row) use ($exists) {
                                            return !in_array($row, $exists);
                                        });
                                        foreach ($sha as $x) {
                                            CommitSHA::create([
                                                "sha" => $x,
                                                "commit_id" => $save->id
                                            ]);
                                        }
                                        $sha = [];
                                    }
                                    if (!$save = Commit::where("repo", $repository->id)
                                        ->where("exact_date", $commitDate)
                                        ->where("branch", $branch->name)
                                        ->first()
                                    ) {
                                        $save = new Commit();
                                        $save->chain = $repository->chain;
                                        $save->repo = $repository->id;
                                        $save->exact_date = $commitDate;
                                        $save->additions = 0;
                                        $save->deletions = 0;
                                        $save->branch = $branch->name;
                                    }
                                    $save->total_commit = 0;
                                    $save->author_list = [];

                                    $date = $commitDate;
                                }
                                $sha[] = $commit->sha;
                                $save->additions += 0;
                                $save->deletions += 0;
                                $save->author_list = array_merge($save->author_list, [$author]);
                                $save->total_commit += 1;

                                if ($z == count($data) - 1) {
                                    $save->author_list = implode(",", $save->author_list);
                                    $save->save();
                                }
                            }
                        }
                    }

                    /**
                     * Summarize Developer
                     */
                    $cms = Commit::where("chain", $chain->id)
                        ->where("repo", $repository->id)
                        ->where("exact_date", ">=", $last)
                        ->orderBy("exact_date", "ASC")
                        ->get();
                    foreach ($cms as $item) {
                        $day = Carbon::createFromTimestamp(strtotime($item->exact_date));
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
                        $saving = [
                            "full_time" => [],
                            "part_time" => [],
                        ];
                        foreach ($authors as $author => $commits) {
                            if (isset($last30DayAuthors[$author]))
                                $commits += $last30DayAuthors[$author];
                            if ($commits > 10) {
                                $saving["full_time"][] = $author;
                            }
                            if ($commits <= 10) {
                                $saving["part_time"][] = $author;
                            }
                        }

                        $item->full_time = implode(",", $saving["full_time"]);
                        $item->part_time = implode(",", $saving["part_time"]);
                        $item->total_full_time = count($saving["full_time"]);
                        $item->total_part_time = count($saving["part_time"]);
                        $item->save();
                    }
                }
            } catch (\Exception $exception) {
                send_telegram_message("Chain " . $chain->id . "-" . $chain->name . " have exception: " . implode(". ", [$exception->getMessage(), $exception->getCode(), $exception->getLine()]));
                break;
            }
//            break;
        }
        send_telegram_message("Get commits " . now("Asia/Bangkok")->toDateTimeString());
    }
}
