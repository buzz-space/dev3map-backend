<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Contributor;
use Botble\Statistic\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetDeveloperInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'developer:info';

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
        $repo = $this->ask("Begin repo");
        foreach (Repository::where("chain", 4)->orderBy("id", "ASC")->get() as $item) {
            echo "Repository " . $item->id . "-" . $item->name . PHP_EOL;
            $contributors = array_filter(explode(",", $item->total_contributor));
            foreach ($contributors as $contributor) {
                if ($found = Contributor::where("login", $contributor)->first()) {
                    $found->chain = implode(",", array_unique(array_merge(explode(",", $found->chain), [$item->chain])));
                    $found->repo = implode(",", array_unique(array_merge(explode(",", $found->repo), [$item->id])));
                    $found->save();

                    echo "Processed contributor $contributor" . PHP_EOL;
                } else {
                    echo "Start created contributor $contributor" . PHP_EOL;
                    $data = json_decode(get_github_data("https://api.github.com/users/$contributor", 1, 2));
                    if (isset($data->message)) {
                        if ($data->message == "Not found")
                            continue;
                        echo "Error: " . $data->message . PHP_EOL;
                        break;
                    }
                    Contributor::create([
                        "chain" => $item->chain,
                        "repo" => $item->id,
                        "login" => $contributor,
                        "name" => $data->name ?? $contributor,
                        "description" => $data->bio,
                        "avatar" => $data->avatar_url,
                    ]);

                }
            }
        }
    }
}
