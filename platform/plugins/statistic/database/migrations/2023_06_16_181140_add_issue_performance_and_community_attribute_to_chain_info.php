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
            $table->float("issue_performance")->default(0)->after("part_time");
            $table->float("community_attribute")->default(0)->after("issue_performance");

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
            $table->dropColumn("issue_performance");
            $table->dropColumn("community_attribute");
        });
    }
};
