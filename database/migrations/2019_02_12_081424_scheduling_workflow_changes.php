<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SchedulingWorkflowChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_task_types', function (Blueprint $table) {
            $table->boolean('allow_edit_due_date')->default(true);
        });

        Schema::table('job_tasks', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable()->change();
            $table->dateTime('ends_at')->nullable()->change();
        });

        Schema::create('job_task_scheduled_portion_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_task_id');
            $table->bigInteger('user_id')->nullable();
            $table->text('status');
            $table->text('reason')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('job_task_id')
                ->references('id')
                ->on('job_tasks')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('job_task_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_task_types', function (Blueprint $table) {
            $table->dropColumn('allow_edit_due_date');
        });

        Schema::table('job_tasks', function (Blueprint $table) {
            $table->dateTime('starts_at')->change();
            $table->dateTime('ends_at')->change();
        });

        Schema::dropIfExists('job_task_scheduled_portion_statuses');
    }
}
