<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chain_info', function (Blueprint $table) {
            $table->unsignedInteger("full_time_developer")->default(0)->after("total_developer");
            $table->unsignedInteger("part_time_developer")->default(0)->after("full_time_developer");
            $table->dropColumn("total_developer");
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chain_info', function (Blueprint $table) {
            $table->unsignedInteger("total_developer")->default(0)->after("part_time_developer");
            $table->dropColumn("full_time_developer");
            $table->dropColumn("part_time_developer");
        });
    }
};
