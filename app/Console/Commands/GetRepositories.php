<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Repository;
use Illuminate\Console\Command;

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
        foreach ($chains as $chain){
            echo "Chain " . $chain->name . PHP_EOL;
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
        }


        echo "Done";
    }
}
