<?php

namespace Database\Seeders;

use Botble\Customers\Models\Earn;
use Botble\Customers\Models\Missions;
use Illuminate\Database\Seeder;

class EarnSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create mission
        $news = Earn::create([
            "task" => Earn::TASK_NEWS,
            "exp_earn" => 20
        ]);

        $guide = Earn::create([
            "task" => Earn::TASK_AZ_GUIDE,
            "exp_earn" => 30,
            "buzz_earn" => 0,
            "description" => "Earn 2 buzz if complete all a-z guide"
        ]);

        $buzz = Earn::create([
            "task" => Earn::TASK_BUZZ_SPECIAL,
            "exp_earn" => 20,
            "buzz_earn" => 0
        ]);

        $game = Earn::create([
            "task" => Earn::TASK_GAME,
            "exp_earn" => 30,
            "buzz_earn" => 2,
            "description" => "Earn 2 buzz if overall 80 point"
        ]);

        Missions::create([
            "type" => Missions::TYPE_DAILY,
            "earn_id" => $news->id,
            "count" => 1
        ]);

        Missions::create([
            "type" => Missions::TYPE_DAILY,
            "earn_id" => $guide->id,
            "count" => 1
        ]);

        Missions::create([
            "type" => Missions::TYPE_DAILY,
            "earn_id" => $buzz->id,
            "count" => 1
        ]);

        Missions::create([
            "type" => Missions::TYPE_DAILY,
            "earn_id" => $game->id,
            "count" => 2
        ]);

        Missions::create([
            "type" => Missions::TYPE_WEEKLY,
            "earn_id" => $news->id,
            "count" => 10
        ]);

        Missions::create([
            "type" => Missions::TYPE_WEEKLY,
            "earn_id" => $guide->id,
            "count" => 10
        ]);

        Missions::create([
            "type" => Missions::TYPE_WEEKLY,
            "earn_id" => $buzz->id,
            "count" => 10
        ]);

        Missions::create([
            "type" => Missions::TYPE_WEEKLY,
            "earn_id" => $game->id,
            "count" => 15
        ]);

        Earn::create([
            "task" => Earn::TASK_PREDICT,
            "exp_earn" => 20,
            "buzz_earn" => 2,
        ]);

        Earn::create([
            "task" => Earn::TASK_CORRECT_PREDICT,
            "exp_earn" => 0,
            "buzz_earn" => 5,
        ]);

        Earn::create([
            "task" => Earn::TASK_POST_LIKE,
            "exp_earn" => 10,
            "buzz_earn" => 0,
        ]);

        Earn::create([
            "task" => Earn::TASK_POST_COMMENT,
            "exp_earn" => 20,
            "buzz_earn" => 0,
        ]);

        Earn::create([
            "task" => Earn::TASK_SHARE,
            "exp_earn" => 30,
            "buzz_earn" => 1,
        ]);

        Earn::create([
            "task" => Earn::TASK_JOURNAL,
            "exp_earn" => 40,
            "buzz_earn" => 3,
        ]);

        Earn::create([
            "task" => Earn::TASK_NFT_ESTIMATE,
            "exp_earn" => 20,
            "buzz_earn" => 0,
        ]);

        Earn::create([
            "task" => Earn::TASK_INACTIVE,
            "exp_earn" => 15,
            "buzz_earn" => 0,
        ]);

    }
}
