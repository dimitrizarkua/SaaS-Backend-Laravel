<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdatedAtFieldToUsersAndTeamsViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
        DB::unprepared(file_get_contents(database_path('sql/replace_users_and_teams_view_add_updated_at.sql')));
        DB::unprepared(file_get_contents(database_path('sql/create_users_and_teams_view_trigger_on_update.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(file_get_contents(database_path('sql/drop_users_and_teams_view_trigger_on_update.sql')));
        DB::unprepared('DROP VIEW IF EXISTS users_and_teams_view');
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
}
