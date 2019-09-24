<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateContactUserNotificationTable
 */
class CreateContactUserNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_user_notification', function (Blueprint $table) {
            $table->bigInteger('user_notification_id');
            $table->bigInteger('contact_id');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_notification_id')
                ->references('id')
                ->on('user_notifications')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['user_notification_id', 'contact_id']);
            $table->index(['contact_id', 'user_notification_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_user_notification');
    }
}
