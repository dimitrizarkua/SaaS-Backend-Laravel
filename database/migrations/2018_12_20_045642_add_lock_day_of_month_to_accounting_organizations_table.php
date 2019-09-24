<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLockDayOfMonthToAccountingOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_organizations', function (Blueprint $table) {
            $table->integer('lock_day_of_month');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_organizations', function (Blueprint $table) {
            $table->dropColumn('lock_day_of_month');
        });
    }
}
