<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobRoomsAndFlooringTypesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createFlooringTypesTable();
        $this->createJobRoomsTable();
    }

    private function createFlooringTypesTable()
    {
        Schema::create('flooring_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();
        });
    }

    private function createJobRoomsTable()
    {
        Schema::create('job_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('flooring_type_id')->nullable();
            $table->text('name');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('flooring_type_id');
            $table->foreign('flooring_type_id')
                ->references('id')
                ->on('flooring_types')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_rooms');
        Schema::dropIfExists('flooring_types');
    }
}
