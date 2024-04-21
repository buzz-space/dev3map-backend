<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetDataForWeek extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-job {commit_from} {summary_on} {commit_chart_on}';

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
        $commitFrom = $this->argument("commit_from");
        $summaryOn = $this->argument("summary_on");
        $commitChartOn = $this->argument("commit_chart_on");

        send_telegram_message("Start on " . now("Asia/Bangkok")->toDateTimeString());

        ini_set("max_execution_time", -1);
        ini_set("memory_limit", -1);
        shell_exec("php artisan get:repositories 0 0 0");
        shell_exec("php artisan get:commits $commitFrom 0 0");
        shell_exec("php artisan summary:info $summaryOn");
        shell_exec("php artisan summary:ranking");
        shell_exec("php artisan ici:info");
        shell_exec("php artisan summary:commit");
        shell_exec("php artisan commit:chart $commitChartOn");
    }
}
