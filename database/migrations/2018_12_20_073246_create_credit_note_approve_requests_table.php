<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNoteApproveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_approve_requests', function (Blueprint $table) {
            $table->bigInteger('requester_id');
            $table->bigInteger('approver_id');
            $table->bigInteger('credit_note_id');
            $table->timestamp('approved_at')->nullable();

            $table->primary(['requester_id', 'approver_id', 'credit_note_id']);

            $table->index('requester_id');
            $table->foreign('requester_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('approver_id');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('credit_note_id');
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_note_approve_requests');
    }
}
