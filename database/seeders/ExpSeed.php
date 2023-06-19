<?php

namespace Database\Seeders;

use Botble\Customers\Models\ExpRequirement;
use Illuminate\Database\Seeder;

class ExpSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ExpRequirement::truncate();
        for ($i = 1; $i <= 100; $i++){
            ExpRequirement::create([
                "level" => $i,
                "exp" => round(4 * ($i * $i * $i * $i) / 5)
            ]);
        }
    }
}
