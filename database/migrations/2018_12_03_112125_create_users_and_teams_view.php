<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersAndTeamsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(file_get_contents(database_path('sql/create_users_and_teams_view.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP VIEW IF EXISTS users_and_teams_view');
    }
}
