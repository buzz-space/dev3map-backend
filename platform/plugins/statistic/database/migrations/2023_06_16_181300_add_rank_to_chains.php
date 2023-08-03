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
            $table->unsignedInteger("commit_rank")->default(0)->after("seriousness");
            $table->unsignedInteger("pull_rank")->default(0)->after("commit_rank");
            $table->unsignedInteger("dev_rank")->default(0)->after("pull_rank");

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
            $table->dropColumn("commit_rank");
            $table->dropColumn("pull_rank");
            $table->dropColumn("dev_rank");
        });
    }
};
