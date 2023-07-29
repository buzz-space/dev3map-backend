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
        Schema::create('chain_info', function (Blueprint $table) {
            $table->increments("id");
            $table->integer("chain");
            $table->string("range")->default("24_hours");
            $table->integer("total_commits")->default(0);
            $table->integer("total_developer")->default(0);
            $table->integer("total_star")->default(0);
            $table->integer("total_fork")->default(0);
            $table->integer("total_repository")->default(0);
            $table->integer("total_issue_solved")->default(0);
            $table->integer("total_pull_merged")->default(0);
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("chain_info");
    }
};
