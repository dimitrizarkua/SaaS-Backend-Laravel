<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactNotesMeetingIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_note', function (Blueprint $table) {
            $table->bigInteger('meeting_id')->nullable();

            $table->foreign('meeting_id')
                ->references('id')
                ->on('meetings')
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
        Schema::table('contact_note', function (Blueprint $table) {
            $table->dropColumn('meeting_id');
        });
    }
}
