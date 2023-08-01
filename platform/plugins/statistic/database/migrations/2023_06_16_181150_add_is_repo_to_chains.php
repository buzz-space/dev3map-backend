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
            $table->boolean("is_repo")->default(false)->after("github_prefix");
            $table->dropColumn("total_commit");
            $table->dropColumn("total_contributor");
            $table->dropColumn("total_developer");
            $table->dropColumn("total_full_time_developer");
            $table->dropColumn("total_part_time_developer");
            $table->dropColumn("total_one_time_developer");
            $table->dropColumn("total_star");
            $table->dropColumn("total_fork");
            $table->dropColumn("total_issue_solved");
            $table->dropColumn("total_pull_request");

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
            $table->dropColumn("is_repo");
        });
    }
};
