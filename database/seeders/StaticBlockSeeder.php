<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Block\Models\Block;
use Botble\Block\Models\BlockTranslation;
use Botble\Language\Models\LanguageMeta;
use Faker\Factory;
use Illuminate\Support\Str;

class StaticBlockSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        Block::truncate();
        BlockTranslation::truncate();
        LanguageMeta::where('reference_type', Block::class)->delete();

        for ($i = 0; $i < 5; $i++) {
            $name = $faker->name;

            $block = Block::create([
                'name'        => $name,
                'alias'       => Str::slug($name),
                'description' => $faker->text(50),
                'content'     => $faker->text(500),
            ]);

            BlockTranslation::insert([
                'lang_code'   => 'vi',
                'blocks_id'   => $block->id,
                'name'        => $block->name,
                'description' => $block->description,
                'content'     => $block->content,
            ]);
        }
    }
}
