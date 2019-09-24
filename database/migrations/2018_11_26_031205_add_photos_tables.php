<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddDocuments
 */
class AddPhotosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('storage_uid');
            $table->text('file_name');
            $table->bigInteger('file_size');
            $table->text('file_hash');
            $table->text('mime_type')->nullable();
            $table->integer('width');
            $table->integer('height');
            $table->bigInteger('original_photo_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('original_photo_id')
                ->references('id')
                ->on('photos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('original_photo_id');
        });

        Schema::create('job_photo', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('photo_id');
            $table->bigInteger('creator_id')->nullable();
            $table->text('description')->nullable();

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('photo_id')
                ->references('id')
                ->on('photos')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['job_id', 'photo_id']);
            $table->index(['photo_id', 'job_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_photo');
        Schema::dropIfExists('photos');
    }
}
