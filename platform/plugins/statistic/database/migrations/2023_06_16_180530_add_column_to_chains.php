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
            $table->string("categories")->nullable()->after("name");
            $table->string("avatar")->nullable()->after("github_prefix");
            $table->string("website")->nullable()->after("avatar");
            $table->unsignedBigInteger("total_pull_request")->default(0)->after("total_issue_solved");
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
            $table->dropColumn("categories");
            $table->dropColumn("avatar");
            $table->dropColumn("website");
            $table->dropColumn("total_pull_request");
        });
    }
};
