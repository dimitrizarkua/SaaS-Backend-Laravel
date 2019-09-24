<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateTeamUserTable
 */
class CreateTeamUserTables extends Migration
{
    /**
     * Create team_user and teams tables.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->bigInteger('user_id');
            $table->bigInteger('team_id');
            $table->primary(['user_id', 'team_id']);
            $table->index(['team_id', 'user_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Drop team_user, teams tables if exists.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
}
