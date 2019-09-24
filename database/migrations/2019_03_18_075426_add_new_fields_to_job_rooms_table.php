<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddNewFieldsToJobRoomsTable
 */
class AddNewFieldsToJobRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_rooms', function (Blueprint $table) {
            $table->decimal('total_sqm', 5, 2)->nullable();
            $table->decimal('affected_sqm', 5, 2)->nullable();
            $table->decimal('non_restorable_sqm', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_rooms', function (Blueprint $table) {
            $table->dropColumn([
                'total_sqm',
                'affected_sqm',
                'non_restorable_sqm',
            ]);
        });
    }
}
