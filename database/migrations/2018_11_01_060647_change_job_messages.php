<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeJobMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('outgoing_job_messages');
        Schema::dropIfExists('incoming_job_messages');

        Schema::create('job_messages', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('message_id');
            $table->primary(['job_id', 'message_id']);

            $table->boolean('is_incoming')->default(false);
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

        Schema::table('job_notes', function (Blueprint $table) {
            $table->timestamp('created_at')->useCurrent();
            $table->dropIndex(['note_id']);

            $table->dropIndex(['outgoing_message_id']);
            $table->dropColumn('outgoing_message_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_messages');

        Schema::table('job_notes', function (Blueprint $table) {
            $table->bigInteger('outgoing_message_id')->nullable();
            $table->dropColumn('created_at');
            $table->index('note_id');

            $table->index('outgoing_message_id');
            $table->foreign('outgoing_message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

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
}
