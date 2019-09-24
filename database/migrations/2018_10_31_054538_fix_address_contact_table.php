<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixAddressContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('address_contact');
        Schema::create('address_contact', function (Blueprint $table) {
            $table->bigInteger('address_id');
            $table->bigInteger('contact_id');
            $table->text('type');

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['address_id', 'contact_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('address_contact');
        Schema::create('address_contact', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('type');
            $table->bigInteger('address_id');
            $table->bigInteger('contact_id');

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->unique(['address_id', 'contact_id', 'type']);
        });
    }
}
