<?php

namespace Database\Seeders;

use Botble\Statistic\Models\ChainInfo;
use Illuminate\Database\Seeder;

class StarForkSeeding extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents(public_path("docs/json/star_fork_14days.json")));
        foreach ($data as $item){
            $chain = ChainInfo::where("chain", $item->chain)->where("range", "before_7_days")->first();
            $chain->total_star = $item->total_star;
            $chain->total_fork = $item->total_fork;
            $chain->save();

            echo "Saved chain " . $item->chain . PHP_EOL;
        }
    }
}
