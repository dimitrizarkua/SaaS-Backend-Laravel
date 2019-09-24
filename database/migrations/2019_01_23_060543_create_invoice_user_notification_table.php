<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceUserNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_user_notification', function (Blueprint $table) {
            $table->bigInteger('invoice_id');
            $table->bigInteger('user_notification_id');

            $table->primary(['user_notification_id', 'invoice_id']);

            $table->index('user_notification_id');
            $table->foreign('user_notification_id')
                ->references('id')
                ->on('user_notifications')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('invoice_id');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
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
        Schema::dropIfExists('invoice_user_notification');
    }
}
