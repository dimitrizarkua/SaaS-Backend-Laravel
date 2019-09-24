<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsIncomingToMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_messages', function (Blueprint $table) {
            $table->dropColumn('is_incoming');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_incoming')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('is_incoming');
        });

        Schema::table('job_messages', function (Blueprint $table) {
            $table->boolean('is_incoming')->default(false);
        });
    }
}
