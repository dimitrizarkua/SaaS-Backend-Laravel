<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsMessagesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createIncomingJobMessagesTable();
        $this->createOutgoingJobMessages();
        $this->createJobNotesTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoming_job_messages');
        Schema::dropIfExists('outgoing_job_messages');
        Schema::dropIfExists('job_notes');
    }

    private function createIncomingJobMessagesTable(): void
    {
        Schema::create('incoming_job_messages', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('message_id');
            $table->primary(['job_id', 'message_id']);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();
            $table->softDeletes();

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('message_id');
            $table->foreign('message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createOutgoingJobMessages(): void
    {
        Schema::create('outgoing_job_messages', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('message_id');
            $table->bigInteger('note_id')->nullable();
            $table->bigInteger('target_contact_id')->nullable();
            $table->primary(['job_id', 'message_id']);

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('message_id');
            $table->foreign('message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('note_id');
            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('target_contact_id');
            $table->foreign('target_contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createJobNotesTable(): void
    {
        Schema::create('job_notes', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('note_id');
            $table->primary(['job_id', 'note_id']);

            $table->bigInteger('job_status_id')->nullable();
            $table->bigInteger('outgoing_message_id')->nullable();
            $table->softDeletes();

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('note_id');
            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('outgoing_message_id');
            $table->foreign('outgoing_message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('job_status_id');
            $table->foreign('job_status_id')
                ->references('id')
                ->on('job_statuses')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }
}
