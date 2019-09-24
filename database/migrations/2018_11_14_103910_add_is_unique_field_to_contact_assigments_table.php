<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddIsUniqueFieldToContactAssigmentsTable
 */
class AddIsUniqueFieldToContactAssigmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_contact_assignment_types', function (Blueprint $table) {
            $table->boolean('is_unique')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_contact_assignment_types', function (Blueprint $table) {
            $table->dropColumn('is_unique');
        });
    }
}
