<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->text('iso_alpha2_code');
            $table->text('iso_alpha3_code');
        });

        Schema::create('states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('country_id');
            $table->text('name');
            $table->text('code');

            $table->index('country_id');
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('suburbs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('state_id');
            $table->text('name');
            $table->text('postcode');

            $table->index('state_id');
            $table->foreign('state_id')
                ->references('id')
                ->on('states')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('contact_name')->nullable();
            $table->bigInteger('contact_phone')->nullable();
            $table->bigInteger('suburb_id')->nullable();
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();

            $table->index('suburb_id');
            $table->foreign('suburb_id')
                ->references('id')
                ->on('suburbs')
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
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('suburbs');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
}
