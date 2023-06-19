<?php

namespace Database\Seeders;

use Botble\Setting\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::whereIn('key', ['media_random_hash'])->delete();

        Setting::insertOrIgnore([
            [
                'key'   => 'media_random_hash',
                'value' => md5(time()),
            ],
        ]);
    }
}
