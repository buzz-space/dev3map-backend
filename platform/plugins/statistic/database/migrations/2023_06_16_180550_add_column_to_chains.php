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
        Schema::table('chains', function (Blueprint $table) {
            $table->unsignedBigInteger("total_developer")->default(0)->after("total_pull_request");
            $table->unsignedBigInteger("total_full_time_developer")->default(0)->after("total_developer");
            $table->unsignedBigInteger("total_part_time_developer")->default(0)->after("total_full_time_developer");
            $table->unsignedBigInteger("total_one_time_developer")->default(0)->after("total_part_time_developer");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chains', function (Blueprint $table) {
            $table->dropColumn("total_developer");
            $table->dropColumn("total_full_time_developer");
            $table->dropColumn("total_part_time_developer");
            $table->dropColumn("total_one_time_developer");
        });
    }
};
