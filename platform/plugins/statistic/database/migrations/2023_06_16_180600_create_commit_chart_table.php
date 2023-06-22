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
        Schema::create('commit_chart', function (Blueprint $table) {
            $table->unsignedInteger("chain");
            $table->unsignedInteger("week")->default(0);
            $table->unsignedInteger("month")->default(0);
            $table->unsignedInteger("year")->default(0);
            $table->unsignedBigInteger("total_commit")->default(0);
            $table->unsignedBigInteger("total_additions")->default(0);
            $table->unsignedBigInteger("total_deletions")->default(0);
            $table->unsignedBigInteger("total_fork_commit")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('commit_chart');
    }
};
