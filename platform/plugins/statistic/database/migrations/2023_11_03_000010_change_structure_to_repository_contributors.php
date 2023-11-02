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
        Schema::table('repository_contributors', function (Blueprint $table) {
            $table->dropColumn("contributors");
            $table->string("chain")->nullable()->change();
            $table->string("repo")->nullable()->change();
            $table->string("name")->after("id")->nullable();
            $table->string("login")->after("name");
            $table->string("description")->after("login")->nullable();
            $table->string("avatar")->after("description")->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repository_contributors', function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("login");
            $table->dropColumn("description");
            $table->dropColumn("avatar");
            $table->string("contributors");
        });
    }
};
