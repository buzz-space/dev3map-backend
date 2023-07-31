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
        Schema::table('developers', function (Blueprint $table) {
            $table->string("full_time")->nullable()->after("total_full_time");
            $table->string("part_time")->nullable()->after("full_time");
            $table->string("one_time")->nullable()->after("part_time");
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
            $table->dropColumn("full_time");
            $table->dropColumn("part_time");
            $table->dropColumn("one_time");
        });
    }
};
