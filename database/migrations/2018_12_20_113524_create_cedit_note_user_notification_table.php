<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCeditNoteUserNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_user_notification', function (Blueprint $table) {
            $table->bigInteger('credit_note_id');
            $table->bigInteger('user_notification_id');

            $table->primary(['credit_note_id', 'user_notification_id']);

            $table->index('credit_note_id');
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('user_notification_id');
            $table->foreign('user_notification_id')
                ->references('id')
                ->on('user_notifications')
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
        Schema::dropIfExists('credit_note_user_notification');
    }
}
