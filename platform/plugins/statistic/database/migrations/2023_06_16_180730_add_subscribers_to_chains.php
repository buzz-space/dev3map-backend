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
            $table->unsignedInteger("subscribers")->default(0)->after("avatar");
        });
        Schema::table("repositories", function (Blueprint $table){
            $table->unsignedInteger("subscribers")->default(0)->after("total_fork");
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
            $table->dropColumn("subscribers");
        });
        Schema::table("repositories", function (Blueprint $table){
            $table->dropColumn("subscribers");
        });
    }
};
