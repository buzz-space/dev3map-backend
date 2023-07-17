<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\Developer;
use Illuminate\Console\Command;

class SummaryInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:info';

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
        $chainId = $this->ask("Chain id?");
        $chain = Chain::whereId($chainId)->first();
        echo "Chain name: " . $chain->name . PHP_EOL;
        $developers = Developer::where("chain", $chain->id)
            ->where("year", 2023)
            ->where("month", "<=", now()->month)
            ->where("month", ">", now()->month - 3)
            ->pluck("author");

        $data = process_developer_string($developers);

        echo print_r($data, true) . PHP_EOL;
    }
}
