<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddressContactUniqueIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address_contact', function (Blueprint $table) {
            $table->unique(['address_id', 'contact_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('address_contact', function (Blueprint $table) {
            $table->dropUnique(['address_id', 'contact_id', 'type']);
        });
    }
}
