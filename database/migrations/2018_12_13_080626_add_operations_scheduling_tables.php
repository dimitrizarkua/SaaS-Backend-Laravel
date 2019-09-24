<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationsSchedulingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_status_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->boolean('makes_vehicle_unavailable')->default(false);
            $table->boolean('is_default');
            $table->softDeletes();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('location_id');
            $table->text('type');
            $table->text('make');
            $table->text('model');
            $table->text('registration');
            $table->timestamp('rent_starts_at')->nullable();
            $table->timestamp('rent_ends_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('location_id');
        });

        Schema::create('vehicle_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');
            $table->bigInteger('vehicle_status_type_id');
            $table->bigInteger('user_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('vehicle_status_type_id')
                ->references('id')
                ->on('vehicle_status_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('vehicle_id');
            $table->index('vehicle_status_type_id');
            $table->index('user_id');
        });

        Schema::create('job_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('location_id');
            $table->text('name')->nullable();
            $table->date('date');

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('location_id');
        });

        Schema::create('job_task_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->boolean('can_be_scheduled')->default(true);
            $table->integer('default_duration_minutes');
            $table->integer('kpi_hours')->nullable();
            $table->boolean('kpi_include_afterhours')->default(false);
            $table->softDeletes();
        });

        Schema::create('job_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('job_task_type_id');
            $table->bigInteger('job_run_id')->nullable();
            $table->text('name')->nullable();
            $table->text('internal_note')->nullable();
            $table->text('scheduling_note')->nullable();
            $table->text('kpi_missed_reason')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('job_task_type_id')
                ->references('id')
                ->on('job_task_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('job_run_id')
                ->references('id')
                ->on('job_runs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('job_id');
            $table->index('job_task_type_id');
            $table->index('job_run_id');
        });

        Schema::create('job_task_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_task_id');
            $table->bigInteger('user_id')->nullable();
            $table->text('status');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

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

        Schema::create('job_run_crew_assignments', function (Blueprint $table) {
            $table->bigInteger('job_run_id');
            $table->bigInteger('crew_user_id');
            $table->bigInteger('assigner_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('job_run_id')
                ->references('id')
                ->on('job_runs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('crew_user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('assigner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_run_id', 'crew_user_id']);
            $table->index(['crew_user_id', 'job_run_id']);
            $table->index('assigner_id');
        });

        Schema::create('job_task_team_assignments', function (Blueprint $table) {
            $table->bigInteger('job_task_id');
            $table->bigInteger('team_id');
            $table->bigInteger('assigner_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('job_task_id')
                ->references('id')
                ->on('job_tasks')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('assigner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_task_id', 'team_id']);
            $table->index(['team_id', 'job_task_id']);
            $table->index('assigner_id');
        });

        Schema::create('job_task_crew_assignments', function (Blueprint $table) {
            $table->bigInteger('job_task_id');
            $table->bigInteger('crew_user_id');
            $table->bigInteger('assigner_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('job_task_id')
                ->references('id')
                ->on('job_tasks')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('crew_user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('assigner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_task_id', 'crew_user_id']);
            $table->index(['crew_user_id', 'job_task_id']);
            $table->index('assigner_id');
        });

        Schema::create('job_run_vehicle_assignments', function (Blueprint $table) {
            $table->bigInteger('job_run_id');
            $table->bigInteger('vehicle_id');
            $table->text('driver_name')->nullable();
            $table->bigInteger('assigner_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('job_run_id')
                ->references('id')
                ->on('job_runs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('assigner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_run_id', 'vehicle_id']);
            $table->index(['vehicle_id', 'job_run_id']);
            $table->index('assigner_id');
        });

        Schema::create('job_task_vehicle_assignments', function (Blueprint $table) {
            $table->bigInteger('job_task_id');
            $table->bigInteger('vehicle_id');
            $table->bigInteger('assigner_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('job_task_id')
                ->references('id')
                ->on('job_tasks')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('assigner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_task_id', 'vehicle_id']);
            $table->index(['vehicle_id', 'job_task_id']);
            $table->index('assigner_id');
        });

        Schema::create('job_run_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('location_id');
            $table->text('name')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('location_id');
        });

        Schema::create('job_run_template_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_run_template_id');
            $table->text('name')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('job_run_template_id')
                ->references('id')
                ->on('job_run_templates')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('job_run_template_id');
        });

        Schema::create('job_run_template_run_crew_assignments', function (Blueprint $table) {
            $table->bigInteger('job_run_template_run_id');
            $table->bigInteger('crew_user_id');

            $table->foreign('job_run_template_run_id')
                ->references('id')
                ->on('job_run_template_runs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('crew_user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_run_template_run_id', 'crew_user_id']);
            $table->index(['crew_user_id', 'job_run_template_run_id']);
        });

        Schema::create('job_run_template_run_vehicle_assignments', function (Blueprint $table) {
            $table->bigInteger('job_run_template_run_id');
            $table->bigInteger('vehicle_id');

            $table->foreign('job_run_template_run_id')
                ->references('id')
                ->on('job_run_template_runs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_run_template_run_id', 'vehicle_id']);
            $table->index(['vehicle_id', 'job_run_template_run_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_run_template_run_crew_assignments');
        Schema::dropIfExists('job_run_template_run_vehicle_assignments');
        Schema::dropIfExists('job_run_template_runs');
        Schema::dropIfExists('job_run_templates');
        Schema::dropIfExists('job_run_crew_assignments');
        Schema::dropIfExists('job_task_crew_assignments');
        Schema::dropIfExists('job_task_team_assignments');
        Schema::dropIfExists('job_run_vehicle_assignments');
        Schema::dropIfExists('job_task_vehicle_assignments');
        Schema::dropIfExists('vehicle_statuses');
        Schema::dropIfExists('vehicle_status_types');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('job_task_statuses');
        Schema::dropIfExists('job_tasks');
        Schema::dropIfExists('job_runs');
        Schema::dropIfExists('job_task_types');
    }
}
