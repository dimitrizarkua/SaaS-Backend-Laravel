<?php

use Illuminate\Database\Migrations\Migration;

class AddTriggerForContactAssignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(file_get_contents(database_path('sql/create_job_contact_assignment_trigger.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(file_get_contents(database_path('sql/drop_job_contact_assignment_trigger.sql')));
    }
}
