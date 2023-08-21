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
            $table->unsignedInteger("total_pull_request")->default(0)->after("total_pull_merged");
        });
        Schema::table('chains', function (Blueprint $table) {
            $table->unsignedInteger("pr_rank")->default(0)->after("star_rank");
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
            $table->dropColumn("total_pull_request");
        });
        Schema::table('chains', function (Blueprint $table) {
            $table->dropColumn("pr_rank");
        });

    }
};
