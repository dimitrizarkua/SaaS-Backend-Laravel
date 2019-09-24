<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserMentionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note_mentions', function (Blueprint $table) {
            $table->bigInteger('note_id');
            $table->bigInteger('user_id');
            $table->primary(['note_id', 'user_id']);
            $table->index(['user_id', 'note_id']);

            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('message_mentions', function (Blueprint $table) {
            $table->bigInteger('message_id');
            $table->bigInteger('user_id');
            $table->primary(['message_id', 'user_id']);
            $table->index(['user_id', 'message_id']);

            $table->foreign('message_id')
                ->references('id')
                ->on('messages')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->text('message_body_resolved')->nullable();
        });
        Schema::table('notes', function (Blueprint $table) {
            $table->text('note_resolved')->nullable();
        });

        DB::statement('UPDATE messages SET message_body_resolved = message_body');
        DB::statement('UPDATE notes SET note_resolved = note');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('note_mentions');
        Schema::dropIfExists('message_mentions');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('message_body_resolved');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('note_resolved');
        });
    }
}
