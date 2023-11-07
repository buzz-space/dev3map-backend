<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contributor_statistic', function (Blueprint $table) {
            $table->id();
            $table->integer("contributor_id");
            $table->string("range")->default("7_days");
            $table->integer("total_commit")->default(0);
            $table->integer("total_pull_request")->default(0);
            $table->integer("total_pull_merged")->default(0);
            $table->integer("total_issue")->default(0);
            $table->float("merge_ratio")->default(0);
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
        Schema::dropIfExists("contributor_statistic");
    }
};
