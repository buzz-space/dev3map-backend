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

        Schema::create('chain_resources', function (Blueprint $table) {
            $table->id();
            $table->integer("chain");
            $table->string("name");
            $table->string("refer_ici");
            $table->string("category")->default("article");
            $table->string("image")->nullable();
            $table->date("created_date")->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("chain_resources");
    }
};
