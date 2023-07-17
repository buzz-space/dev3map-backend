<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Repository;
use Illuminate\Console\Command;

class GetPulls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
        foreach (Repository::all() as $repo) {
            try {
                if ($repo->chain != 4) continue;
                echo "Process repo " . $repo->name . PHP_EOL;
                $prefix = $repo->github_prefix;
                $url = "https://api.github.com/repos/$prefix/pulls?per_page=100";
                $lastPage = get_last_page(get_github_data($url, "header"));
                for ($i = 1; $i <= $lastPage; $i++) {
                    $pageUrl = $url . "&page=$i";
                    $pulls = json_decode(get_github_data($url));
                    if (isset($pulls->message))
                        throw new \Exception($pulls->message);

                }
            } catch (\Exception $exception) {

            }
        }
    }
}
