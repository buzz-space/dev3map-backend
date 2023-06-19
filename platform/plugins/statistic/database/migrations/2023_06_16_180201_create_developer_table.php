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
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->integer("chain");
            $table->integer("repo");
            $table->integer("month")->default(0);
            $table->integer("year")->default(0);
            $table->text("author")->nullable();
            $table->integer("total")->default(0);
            $table->integer("total_one_time")->default(0);
            $table->integer("total_part_time")->default(0);
            $table->integer("total_full_time")->default(0);
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
        Schema::dropIfExists('developers');
    }
};
