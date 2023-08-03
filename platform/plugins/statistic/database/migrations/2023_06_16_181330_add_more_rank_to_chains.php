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
            $table->unsignedInteger("issue_rank")->default(0)->after("pull_rank");
            $table->unsignedInteger("fork_rank")->default(0)->after("issue_rank");
            $table->unsignedInteger("star_rank")->default(0)->after("fork_rank");

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
            $table->dropColumn("issue_rank");
            $table->dropColumn("fork_rank");
            $table->dropColumn("star_rank");
        });
    }
};
