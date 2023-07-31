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
            $table->text("full_time")->nullable()->after("total_fork_commit");
            $table->text("part_time")->nullable()->after("full_time");
            $table->text("one_time")->nullable()->after("part_time");
        });
        Schema::drop("developers");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commits', function (Blueprint $table) {
            $table->dropColumn("full_time");
            $table->dropColumn("part_time");
            $table->dropColumn("one_time");
        });
        Schema::create('developers', function (Blueprint $table) {
            $table->increments("id");
            $table->timestamps();
        });
    }
};
