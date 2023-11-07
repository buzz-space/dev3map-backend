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
        Schema::table('repository_issues', function (Blueprint $table) {
            $table->date("open_date")->nullable()->change();
        });
        Schema::table('repository_issues', function (Blueprint $table) {
            $table->dateTime("open_date")->nullable()->change();
        });
        Schema::table('repository_pulls', function (Blueprint $table) {
            $table->date("created_date")->nullable()->change();
        });
        Schema::table('repository_pulls', function (Blueprint $table) {
            $table->dateTime("created_date")->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
