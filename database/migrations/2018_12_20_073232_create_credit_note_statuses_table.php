<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNoteStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('credit_note_id');
            $table->bigInteger('user_id');
            $table->text('status');
            $table->timestamp('created_at')->useCurrent();

            $table->index('credit_note_id');
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('credit_note_statuses');
    }
}
