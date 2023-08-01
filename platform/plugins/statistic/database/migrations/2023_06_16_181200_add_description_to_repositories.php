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
        Schema::table('repositories', function (Blueprint $table) {
            $table->text("description")->nullable()->after("name");
            $table->unsignedInteger("total_commit")->default(0)->after("total_contributor");
            $table->unsignedInteger("contributors")->default(0)->after("total_contributor");

        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repositories', function (Blueprint $table) {
            $table->dropColumn("description");
            $table->dropColumn("total_commit");
            $table->dropColumn("contributors");
        });
    }
};
