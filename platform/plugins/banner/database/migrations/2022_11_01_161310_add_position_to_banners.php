<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionToBanners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string("position")->default("az-guide")->after("order");
            $table->string("subtitle")->nullable()->after("name");
            $table->dropColumn("name_vi");
            $table->dropColumn("description_vi");
        });
        Schema::create("banners_translations", function (Blueprint $table){
            $table->id();
            $table->string('lang_code');
            $table->integer("banners_id");
            $table->string("name");
            $table->string("subtitle")->nullable();
            $table->text("description")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn("position");
            $table->dropColumn("subtitle");
            $table->string("name_vi");
            $table->string("description_vi");
        });
        Schema::dropIfExists("banners_translations");
    }
}
