<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;

class DatabaseSeeder extends BaseSeeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareRun();

        $this->call(UserSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(GallerySeeder::class);
        $this->call(BlogSeeder::class);
        $this->call(MemberSeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(StaticBlockSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(WidgetSeeder::class);
        $this->call(ThemeOptionSeeder::class);
        $this->call(SettingSeeder::class);
    }
}
