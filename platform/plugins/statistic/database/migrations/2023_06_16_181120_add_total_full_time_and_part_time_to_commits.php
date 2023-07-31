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
        Schema::table('commits', function (Blueprint $table) {
            $table->unsignedInteger("total_full_time")->default(0)->after("part_time");
            $table->unsignedInteger("total_part_time")->default(0)->after("total_full_time");
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commits', function (Blueprint $table) {
            $table->dropColumn("total_full_time");
            $table->dropColumn("total_part_time");
        });
    }
};
