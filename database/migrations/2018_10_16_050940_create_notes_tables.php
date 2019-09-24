<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateNotesTables
 */
class CreateNotesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->text('note');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('document_note', function (Blueprint $table) {
            $table->bigInteger('document_id');
            $table->bigInteger('note_id');

            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['document_id', 'note_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_note');
        Schema::dropIfExists('notes');
    }
}
