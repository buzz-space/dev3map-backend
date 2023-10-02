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

        Schema::table('chains', function (Blueprint $table) {
            $table->dropColumn("commit_rank");
            $table->dropColumn("pull_rank");
            $table->dropColumn("dev_rank");
            $table->dropColumn("issue_rank");
            $table->dropColumn("fork_rank");
            $table->dropColumn("star_rank");
            $table->dropColumn("pr_rank");
            $table->dropColumn("rising_star");
            $table->dropColumn("ibc_astronaut");
            $table->dropColumn("seriousness");
        });

        Schema::table("chain_info", function (Blueprint $table) {
            $table->integer("rising_star")->default(0)->after("community_attribute");
            $table->integer("ibc_astronaut")->default(0)->after("rising_star");
            $table->integer("seriousness")->default(0)->after("ibc_astronaut");
            $table->integer("commit_rank")->default(0)->after("seriousness");
            $table->integer("pull_rank")->default(0)->after("commit_rank");
            $table->integer("dev_rank")->default(0)->after("pull_rank");
            $table->integer("issue_rank")->default(0)->after("dev_rank");
            $table->integer("fork_rank")->default(0)->after("issue_rank");
            $table->integer("star_rank")->default(0)->after("fork_rank");
            $table->integer("pr_rank")->default(0)->after("star_rank");
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
            $table->dropColumn("commit_rank");
            $table->dropColumn("pull_rank");
            $table->dropColumn("dev_rank");
            $table->dropColumn("issue_rank");
            $table->dropColumn("fork_rank");
            $table->dropColumn("star_rank");
            $table->dropColumn("pr_rank");
            $table->dropColumn("rising_star");
            $table->dropColumn("ibc_astronaut");
            $table->dropColumn("seriousness");
        });

        Schema::table("chains", function (Blueprint $table) {
            $table->integer("commit_rank")->default(0)->after("website");
            $table->integer("pull_rank")->default(0)->after("commit_rank");
            $table->integer("dev_rank")->default(0)->after("pull_rank");
            $table->integer("issue_rank")->default(0)->after("dev_rank");
            $table->integer("fork_rank")->default(0)->after("issue_rank");
            $table->integer("star_rank")->default(0)->after("fork_rank");
            $table->integer("pr_rank")->default(0)->after("star_rank");
            $table->integer("rising_star")->default(0)->after("website");
            $table->integer("ibc_astronaut")->default(0)->after("rising_star");
            $table->integer("seriousness")->default(0)->after("ibc_astronaut");
        });
    }
};
