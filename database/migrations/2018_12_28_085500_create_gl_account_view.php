<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateGLAccountView
 */
class CreateGLAccountView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(file_get_contents(database_path('sql/create_gl_accounts_view.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP VIEW IF EXISTS gl_accounts_view');
    }
}
