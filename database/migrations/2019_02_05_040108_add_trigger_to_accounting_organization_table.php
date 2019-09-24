<?php

use Illuminate\Database\Migrations\Migration;

class AddTriggerToAccountingOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(file_get_contents(database_path('sql/create_active_accounting_organization_trigger.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(file_get_contents(database_path('sql/drop_active_accounting_organization_trigger.sql')));
    }
}
