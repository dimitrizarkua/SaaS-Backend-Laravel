<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class ChangeContactPhoneFieldInAddressesTable
 */
class ChangeContactPhoneFieldInAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->text('contact_phone')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Column cannot be cast automatically to type bigint.
        DB::statement(
            'ALTER TABLE addresses ALTER COLUMN contact_phone TYPE BIGINT USING (trim(contact_phone)::BIGINT)'
        );
    }
}
