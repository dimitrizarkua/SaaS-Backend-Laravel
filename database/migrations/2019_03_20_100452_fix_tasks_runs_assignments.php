<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixTasksRunsAssignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_tasks', function (Blueprint $table) {
            $table->dropForeign('job_tasks_job_run_id_foreign');
            $table->foreign('job_run_id')
                ->references('id')
                ->on('job_runs')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_tasks', function (Blueprint $table) {
            $table->dropForeign('job_tasks_job_run_id_foreign');
            $table->foreign('job_run_id')
                ->references('id')
                ->on('job_runs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
}
