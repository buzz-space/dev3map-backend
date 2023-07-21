<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Pull;
use Botble\Statistic\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetPulls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:pull';

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
    public function handle()
    {
        foreach (Repository::orderBy("chain", "ASC")->get() as $repo) {
            try {
                if ($repo->chain != 4) continue;
                echo "Process repo " . $repo->name . PHP_EOL;
                $prefix = $repo->github_prefix;
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
