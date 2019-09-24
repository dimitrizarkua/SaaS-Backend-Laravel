<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sender_user_id')->nullable();
            $table->text('message_type');
            $table->text('from_address')->nullable();
            $table->text('from_name')->nullable();
            $table->text('subject')->nullable();
            $table->text('message_body');
            $table->text('external_system_message_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('sender_user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('sender_user_id');
        });

        Schema::create('message_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('message_id');
            $table->text('status');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('message_id');
        });

        Schema::create('message_recipients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('message_id');
            $table->text('type');
            $table->text('name')->nullable();
            $table->text('address');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('message_id');
        });

        Schema::create('document_message', function (Blueprint $table) {
            $table->bigInteger('document_id');
            $table->bigInteger('message_id');
            $table->primary(['document_id', 'message_id']);

            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_message');
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('message_statuses');
        Schema::dropIfExists('messages');
    }
}
