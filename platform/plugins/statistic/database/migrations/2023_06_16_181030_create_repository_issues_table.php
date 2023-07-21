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
        Schema::create('repository_issues', function (Blueprint $table) {
            $table->increments("id");
            $table->integer("issue_id");
            $table->integer("repo");
            $table->integer("chain");
            $table->string("creator");
            $table->dateTime("open_date");
            $table->dateTime("close_date")->nullable();
            $table->unsignedBigInteger("total_minute")->default(0);
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
        Schema::drop("repository_issues");
    }
};
