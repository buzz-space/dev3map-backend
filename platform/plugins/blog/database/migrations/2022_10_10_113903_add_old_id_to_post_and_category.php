<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOldIdToPostAndCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string("coc_id")->nullable();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->string("coc_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn("coc_id");
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn("coc_id");
        });
    }
}
