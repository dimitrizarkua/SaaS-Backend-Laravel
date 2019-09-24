<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCeditNoteTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_transaction', function (Blueprint $table) {
            $table->bigInteger('credit_note_id');
            $table->bigInteger('transaction_id');

            $table->primary(['credit_note_id', 'transaction_id']);

            $table->index('credit_note_id');
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('transaction_id');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
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
        Schema::dropIfExists('credit_note_transaction');
    }
}
