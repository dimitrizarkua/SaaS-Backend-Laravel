<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNoteTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_tag', function (Blueprint $table) {
            $table->bigInteger('credit_note_id');
            $table->bigInteger('tag_id');

            $table->primary(['credit_note_id', 'tag_id']);

            $table->index('credit_note_id');
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('tag_id');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
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
        Schema::dropIfExists('credit_note_tag');
    }
}
