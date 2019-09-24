<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateUserNotificationsTable
 */
class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->text('type');
            $table->text('body')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at');
            $table->index('expires_at');

            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->index('user_id');
        });

        Schema::create('job_user_notification', function (Blueprint $table) {
            $table->bigInteger('user_notification_id');
            $table->bigInteger('job_id');

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_notification_id')
                ->references('id')
                ->on('user_notifications')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['user_notification_id', 'job_id']);
            $table->index(['job_id', 'user_notification_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_user_notification');
        Schema::dropIfExists('user_notifications');
    }
}
