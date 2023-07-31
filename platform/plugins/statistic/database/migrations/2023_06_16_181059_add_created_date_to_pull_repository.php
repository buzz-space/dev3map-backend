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
        Schema::table('repository_pulls', function (Blueprint $table) {
            $table->dateTime("created_date")->nullable()->after("author");
        });
        Schema::table('repositories', function (Blueprint $table) {
            $table->dateTime("created_date")->nullable()->after("chain");
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repository_pulls', function (Blueprint $table) {
            $table->dropColumn("created_date");
        });
        Schema::table('repositories', function (Blueprint $table) {
            $table->dropColumn("created_date");
        });
    }
};
