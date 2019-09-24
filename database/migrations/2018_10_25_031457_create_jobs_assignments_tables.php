<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsAssignmentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createJobUserAssignmentsTable();
        $this->createJobFollowersTable();
        $this->createJobContactAssignmentsTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_contact_assignments');
        Schema::dropIfExists('job_contact_assignment_types');
        Schema::dropIfExists('job_user_assignments');
        Schema::dropIfExists('job_followers');
    }

    private function createJobUserAssignmentsTable(): void
    {
        Schema::create('job_user_assignments', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('user_id');
            $table->primary(['job_id', 'user_id']);
            $table->index(['user_id', 'job_id']);

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createJobFollowersTable(): void
    {
        Schema::create('job_followers', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('user_id');
            $table->primary(['job_id', 'user_id']);
            $table->index(['user_id', 'job_id']);

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    private function createJobContactAssignmentsTable(): void
    {
        Schema::create('job_contact_assignment_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
        });

        Schema::create('job_contact_assignments', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('job_assignment_type_id');
            $table->bigInteger('assignee_contact_id');
            $table->primary(['job_id', 'job_assignment_type_id', 'assignee_contact_id']);

            $table->bigInteger('assigner_id')->nullable();
            $table->boolean('invoice_to');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('job_assignment_type_id');
            $table->foreign('job_assignment_type_id')
                ->references('id')
                ->on('job_contact_assignment_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('assignee_contact_id');
            $table->foreign('assignee_contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('assigner_id');
            $table->foreign('assigner_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }
}
