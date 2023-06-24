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
        Schema::table('commit_chart', function (Blueprint $table) {
            $table->increments("id")->first();
            $table->timestamps();
            $table->date("from")->nullable()->after("week");
            $table->date("to")->nullable()->after("from");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commit_chart', function (Blueprint $table) {

            $table->dropColumn("from");
            $table->dropColumn("to");
        });
    }
};
