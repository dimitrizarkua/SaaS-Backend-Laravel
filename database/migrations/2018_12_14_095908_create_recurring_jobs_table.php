<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateRecurringJobsTable
 */
class CreateRecurringJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('recurrence_rule');

            $table->bigInteger('insurer_id');
            $table->index('insurer_id');
            $table->foreign('insurer_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->bigInteger('job_service_id');
            $table->index('job_service_id');
            $table->foreign('job_service_id')
                ->references('id')
                ->on('job_services')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->bigInteger('site_address_id');
            $table->index('site_address_id');
            $table->foreign('site_address_id')
                ->references('id')
                ->on('addresses')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->bigInteger('owner_location_id');
            $table->index('owner_location_id');
            $table->foreign('owner_location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->text('description');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recurring_jobs');
    }
}
