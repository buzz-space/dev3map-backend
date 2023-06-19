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
        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("github_prefix");
            $table->unsignedInteger("total_contributor")->default(0);
            $table->unsignedInteger("total_issue_solved")->default(0);
            $table->unsignedInteger("total_star")->default(0);
            $table->unsignedInteger("total_fork")->default(0);
            $table->unsignedInteger("chain");
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
        Schema::dropIfExists('repositories');
    }
};
