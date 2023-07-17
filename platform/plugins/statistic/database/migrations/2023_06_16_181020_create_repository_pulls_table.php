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
        Schema::create('repository_pulls', function (Blueprint $table) {
            $table->increments("id");
            $table->integer("repo");

            $table->timestamps();
        });

        Schema::table('commits', function (Blueprint $table) {
            $table->text("author_list")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("commit_sha");
    }
};
