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
        Schema::table('developers', function (Blueprint $table) {
            $table->unsignedInteger("total_commit")->default(0)->after("author");
            $table->dropColumn("total");
            $table->unsignedBigInteger("total_developer")->default(0)->after("total_commit");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('developers', function (Blueprint $table) {
            $table->dropColumn("total_commit");
            $table->dropColumn("total_developer");
            $table->unsignedBigInteger("total")->default(0)->after("author");
        });
    }
};
