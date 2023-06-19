<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->integer("post_id")->default(0);
            $table->integer("post_cr4_id")->default(0);
            $table->integer("user_id");
            $table->text("comment")->nullable();
            $table->integer("comment_id")->default(0);
            $table->bigInteger("likes")->default(0);
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
        Schema::dropIfExists('post_comments');
    }
}
