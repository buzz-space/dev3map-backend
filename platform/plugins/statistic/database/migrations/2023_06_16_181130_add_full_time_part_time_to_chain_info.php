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
            $table->text("full_time")->nullable()->after("full_time_developer");
            $table->text("part_time")->nullable()->after("part_time_developer");

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
            $table->dropColumn("full_time");
            $table->dropColumn("part_time");
        });
    }
};
