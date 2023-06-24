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
            'Infrastructure,Web3', 'Infrastructure', 'Finance', 'Web3,Social,Games', 'Infrastructure'];
        foreach ($chains as $i => $chain){
            echo "Chain " . $chain->name . PHP_EOL;
            try{
                $chainUrl = "https://api.github.com/orgs/" . $chain->github_prefix;
                $chainInfo = json_decode(get_github_data($chainUrl));
                $chain->avatar = $chainInfo->avatar_url ? $chainInfo->avatar_url : null;
                $chain->name = $chainInfo->name ?? ucfirst(utf8convert($chain->login));
                $chain->website = $chainInfo->blog;
                $chain->categories = $categories[$i];

                // Get all repository from chain (test aura-nw)
                $prefix = $chain->github_prefix;
                $url = "https://api.github.com/orgs/$prefix/repos?per_page=100";
                $lastPage = get_last_page(get_github_data($url, "header"));
                $repository = [];
                for ( $i = 1; $i <= $lastPage; $i++){
                    $repository = array_merge($repository, array_column( (array) json_decode(get_github_data($url . "&page=$i")), "full_name", "name"));
                }

                foreach ($repository as $name => $prefix){
                    if (!$repo = Repository::where("github_prefix", $prefix)->first()){
                        $repo = new Repository();
                        $repo->name = $name;
                        $repo->github_prefix = $prefix;
                        $repo->chain = $chain->id;
                        $repo->save();
                        echo "Created repository " . $name . " of chain " . $chain->name . PHP_EOL;
                    }
                }

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
