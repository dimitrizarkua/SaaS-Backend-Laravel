<?php

use Illuminate\Database\Migrations\Migration;

class FixGlAccountsViewAddTaxRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(file_get_contents(database_path('sql/replace_gl_accounts_view_add_tax_rate.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP VIEW gl_accounts_view');
    }
}
