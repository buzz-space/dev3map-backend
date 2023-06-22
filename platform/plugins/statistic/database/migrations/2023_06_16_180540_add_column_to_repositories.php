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
            $table->unsignedInteger("additions")->default(0)->after("total_commit");
            $table->unsignedInteger("deletions")->default(0)->after("additions");
            $table->unsignedInteger("total_fork_commit")->default(0)->after("deletions");
        });
        Schema::table('repositories', function (Blueprint $table) {
            $table->unsignedInteger("pull_request_closed")->default(0)->after("total_issue_solved");
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
            $table->dropColumn("additions");
            $table->dropColumn("deletions");
            $table->dropColumn("total_fork_commit");
        });

        Schema::table('repositories', function (Blueprint $table) {
            $table->dropColumn("pull_request_closed");
        });
    }
};
