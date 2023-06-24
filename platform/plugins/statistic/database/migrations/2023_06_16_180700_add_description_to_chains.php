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
            $table->text("description")->nullable()->after("name");
            $table->unsignedFloat("rising_star")->default(0)->after("total_fork");
            $table->unsignedFloat("ibc_astronaut")->default(0)->after("rising_star");
            $table->unsignedFloat("seriousness")->default(0)->after("ibc_astronaut");
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
            $table->dropColumn("description");
            $table->dropColumn("rising_star");
            $table->dropColumn("ibc_astronaut");
            $table->dropColumn("seriousness");
        });
    }
};
