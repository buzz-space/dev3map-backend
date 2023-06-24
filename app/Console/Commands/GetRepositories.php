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
        $sortByCommit = Chain::orderBY("total_commit", "DESC")->pluck("id")->toArray();
        $sortByIssue = Chain::orderBY("total_issue_solved", "DESC")->pluck("id")->toArray();
        $sortByPRSolved = Chain::orderBY("total_pull_request", "DESC")->pluck("id")->toArray();
        $sortByDeveloper = Chain::orderBY("total_developer", "DESC")->pluck("id")->toArray();
        $sortByFork = Chain::orderBY("total_fork", "DESC")->pluck("id")->toArray();
        $sortByStar = Chain::orderBY("total_star", "DESC")->pluck("id")->toArray();
        $categories = ['Infrastructure', 'Data', 'NFT', 'NFT', 'Infrastructure,Bridge', 'Infrastructure,Oracle', 'Payment',
            'NFT,Social,Marketplace', 'Infrastructure', 'Meme', 'Infrastructure,Finance', 'Infrastructure',
            'Finance', 'Infrastructure', 'Infrastructure,Web3', 'Infrastructure,Social', 'Finance', 'Infrastructure',
            'Infrastructure,Finance', 'Bridge', 'Infrastructure,Finance', 'Infrastructure', 'Infrastructure,Finance,Social',
            'Infrastructure', 'Finance', 'Infrastructure,Finance', 'Finance', 'Finance', 'Infrastructure,Data',
            'Infrastructure,Social', 'Infrastructure,Web3', 'Finance', 'Infrastructure', 'Data,Social', 'Infrastructure',
            'Infrastructure', 'NFT,Infrastructure,Social', 'Finance', 'Finance', 'Web3', 'Finance,Bridge', 'Infrastructure,Finance',
            'Finance', 'Finance', 'Infrastructure,Social', 'Infrastructure,Finance', 'Privacy', 'Infrastructure,Privacy',
            'Infrastructure,Privacy', 'Finance', 'Finance', 'Finance', 'NFT,Social,Marketplace', 'Name Service', 'Finance',
            'Infrastructure,Web3', 'Infrastructure', 'Finance', 'Web3,Social,Games', 'Infrastructure'];
        foreach ($chains as $i => $chain){
            echo "Chain " . $chain->name . PHP_EOL;
            try{
                $chainUrl = "https://api.github.com/orgs/" . $chain->github_prefix;
                $chainInfo = json_decode(get_github_data($chainUrl));
//                $chain->avatar = $chainInfo->avatar_url ? $chainInfo->avatar_url : null;
//                $chain->name = $chainInfo->name ?? ucfirst(utf8convert($chain->login));
                $chain->website = $chainInfo->blog;
                $chain->description = $chainInfo->description;
//                $chain->categories = $categories[$i];
                $commitRank = count($chains) - array_search($chain->id, $sortByCommit) + 1;
                $issueRank = count($chains) - array_search($chain->id, $sortByIssue) + 1;
                $PRSolvedRank = count($chains) - array_search($chain->id, $sortByPRSolved) + 1;
                $developerRank = count($chains) - array_search($chain->id, $sortByDeveloper) + 1;
                $forkRank = count($chains) - array_search($chain->id, $sortByFork) + 1;
                $starRank = count($chains) - array_search($chain->id, $sortByStar) + 1;
                $chain->seriousness = round($commitRank / 100 * 35, 2) + round($issueRank / 100 * 20, 2)
                    + round($PRSolvedRank / 100 * 20, 2) + round($developerRank / 100 * 25, 2);
                $chain->rising_star = round($forkRank / 100 * 65, 2) + round($starRank / 100 * 35, 2);
                $chain->ibc_astronaut = round($commitRank / 100 * 50, 2) + round($issueRank / 100 * 20, 2)
                    + round($PRSolvedRank / 100 * 30, 2);
                // Get all repository from chain (test aura-nw)
//                $prefix = $chain->github_prefix;
//                $url = "https://api.github.com/orgs/$prefix/repos?per_page=100";
//                $lastPage = get_last_page(get_github_data($url, "header"));
//                $repository = [];
//                for ( $i = 1; $i <= $lastPage; $i++){
//                    $repository = array_merge($repository, array_column( (array) json_decode(get_github_data($url . "&page=$i")), "full_name", "name"));
//                }
//
//                foreach ($repository as $name => $prefix){
//                    if (!$repo = Repository::where("github_prefix", $prefix)->first()){
//                        $repo = new Repository();
//                        $repo->name = $name;
//                        $repo->github_prefix = $prefix;
//                        $repo->chain = $chain->id;
//                        $repo->save();
//                        echo "Created repository " . $name . " of chain " . $chain->name . PHP_EOL;
//                    }
//                }

                $chain->last_updated = now();
                $chain->save();
            } catch (\Exception $exception){
                echo $exception->getMessage() . PHP_EOL;
                continue;
            }
        }


        echo "Done";
    }
}
