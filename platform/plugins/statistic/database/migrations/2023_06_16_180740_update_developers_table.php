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
            $table->date("day")->nullable()->after("month");
            $table->dropColumn("month");
            $table->dropColumn("year");
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
            $table->unsignedInteger("month")->after("day");
            $table->unsignedInteger("year")->after("month");
            $table->dropColumn("day");
        });
    }
};
