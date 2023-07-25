<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Repository;
use Illuminate\Console\Command;
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
        $chains = Chain::all();

        $categories = ['Infrastructure', 'Data', 'NFT', 'NFT', 'Infrastructure,Bridge', 'Infrastructure,Oracle', 'Payment',
            'NFT,Social,Marketplace', 'Infrastructure', 'Meme', 'Infrastructure,Finance', 'Infrastructure',
            'Finance', 'Infrastructure', 'Infrastructure,Web3', 'Infrastructure,Social', 'Finance', 'Infrastructure',
            'Infrastructure,Finance', 'Bridge', 'Infrastructure,Finance', 'Infrastructure', 'Infrastructure,Finance,Social',
            'Infrastructure', 'Finance', 'Infrastructure,Finance', 'Finance', 'Finance', 'Infrastructure,Data',
            'Infrastructure,Social', 'Infrastructure,Web3', 'Finance', 'Infrastructure', 'Data,Social', 'Infrastructure',
            'Infrastructure', 'NFT,Infrastructure,Social', 'Finance', 'Finance', 'Web3', 'Finance,Bridge', 'Infrastructure,Finance',
            'Finance', 'Finance', 'Infrastructure,Social', 'Infrastructure,Finance', 'Privacy', 'Infrastructure,Privacy',
            'Infrastructure,Privacy', 'Finance', 'Finance', 'Finance', 'NFT,Social,Marketplace', 'Name Service', 'Finance',
            'Infrastructure,Web3', 'Infrastructure', 'Finance', 'Web3,Social,Games', 'Infrastructure', 'Infrastructure', 'Infrastructure,Data'];
        foreach ($chains as $i => $chain) {
            echo "Chain " . $chain->name . PHP_EOL;
//            if (!in_array($chain->id, [27, 43, 60])) continue;
            try {
//                $chainUrl = "https://api.github.com/orgs/" . $chain->github_prefix;
//                $chainInfo = json_decode(get_github_data($chainUrl));
//                $chain->avatar = $chainInfo->avatar_url ? $chainInfo->avatar_url : null;
//                $chain->name = $chainInfo->name ?? ucfirst(utf8convert($chain->login));
//                $chain->website = $chainInfo->blog;
//                $chain->description = $chainInfo->description;
//                $chain->categories = $categories[$i];
                // Get all repository from chain (test aura-nw)
                $prefix = $chain->github_prefix;
                $url = "https://api.github.com/orgs/$prefix/repos?per_page=100";
                $lastPage = get_last_page(get_github_data($url, "header"));
                $repository = [];
                for ($i = 1; $i <= $lastPage; $i++) {
                    $repository = array_merge($repository, array_column((array)json_decode(get_github_data($url . "&page=$i")), "full_name", "name"));
                }

//                echo print_r($repository, true) . PHP_EOL; return;

                foreach ($repository as $name => $repoPrefix) {
                    $repoUrl = "https://api.github.com/repos/$repoPrefix";
                    echo "Repo " . $repoUrl . " of chain " . $chain->name . PHP_EOL;
                    $repoInfo = json_decode(get_github_data($repoUrl));
                    if (isset($repoInfo->message) && $repoInfo->message == "Git Repository is empty.")
                        continue;
                    if (!$repo = Repository::where("github_prefix", $repoPrefix)->first()) {
                        $repo = new Repository();
                        $repo->name = $name;
                        $repo->github_prefix = $repoPrefix;
                        $repo->chain = $chain->id;
                        $repo->save();
                        echo "Created repository " . $name . " of chain " . $chain->name . PHP_EOL;
                    }
                    $repo->subscribers = $repoInfo->subscribers_count;
                    $repo->total_star = $repoInfo->stargazers_count;
                    $repo->total_fork = $repoInfo->forks_count;

                    $issueUrl = "https://api.github.com/repos/$repoPrefix/issues?per_page=100&state=closed";
                    $issueLastPage = get_last_page(get_github_data($issueUrl, "header"));
//                    echo $issueUrl . "&page=$issueLastPage" . PHP_EOL; return;
//                    echo print_r(json_decode(get_github_data($issueUrl . "&page=$issueLastPage")), true) . PHP_EOL;
                    $totalIssueLastPage = count(json_decode(get_github_data($issueUrl . "&page=$issueLastPage")));
//                    echo "Pass count 1" . PHP_EOL;
                    $repo->total_issue_solved = (($issueLastPage - 1) * 100 + $totalIssueLastPage);

                    $pullUrl = "https://api.github.com/repos/$repoPrefix/pulls?per_page=100&state=closed";
                    $pullLastPage = get_last_page(get_github_data($pullUrl, "header"));
                    $totalPullLastPage = count(json_decode(get_github_data($pullUrl . "&page=$pullLastPage")));
//                    echo "Pass count 2" . PHP_EOL;
                    $repo->pull_request_closed = (($pullLastPage - 1) * 100 + $totalPullLastPage);

                    $contributorUrl = "https://api.github.com/repos/$repoPrefix/contributors?per_page=100";
                    $contributorLastPage = get_last_page(get_github_data($contributorUrl, "header"));
                    $contributors = [];
                    for ( $i = 1; $i <= $contributorLastPage; $i++){
                        $contributors = array_merge($contributors, array_column( (array) json_decode(get_github_data($contributorUrl . "&page=$i")), "login"));
                    }
                    $repo->total_contributor = implode(",", $contributors);

                    $repo->save();

//                    $chain->subscribers += $repoInfo->subscribers_count;
                }

                $chain->last_updated = now();
                $chain->save();
            } catch (\Exception $exception) {
                echo $exception->getMessage() . PHP_EOL;
                continue;
            }
        }


        echo "Done" . PHP_EOL;
    }
}
