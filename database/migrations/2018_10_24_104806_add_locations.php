<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('code')->unique();
            $table->text('name')->unique();
        });

        Schema::create('location_user', function (Blueprint $table) {
            $table->bigInteger('location_id');
            $table->bigInteger('user_id');
            $table->boolean('primary')->default(false);

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['location_id', 'user_id']);
            $table->index(['user_id', 'location_id']);
        });

        Schema::create('location_suburb', function (Blueprint $table) {
            $table->bigInteger('location_id');
            $table->bigInteger('suburb_id');

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('suburb_id')
                ->references('id')
                ->on('suburbs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['location_id', 'suburb_id',]);
            $table->index(['suburb_id', 'location_id']);
        });

        DB::unprepared(file_get_contents(database_path('sql/create_primary_location_trigger.sql')));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(file_get_contents(database_path('sql/drop_primary_location_trigger.sql')));

        Schema::dropIfExists('location_suburb');
        Schema::dropIfExists('location_user');
        Schema::dropIfExists('locations');
    }
}
