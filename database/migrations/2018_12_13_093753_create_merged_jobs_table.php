<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateMergedJobsTable
 */
class CreateMergedJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merged_jobs', function (Blueprint $table) {
            $table->bigInteger('source_job_id');
            $table->bigInteger('destination_job_id');

            $table->foreign('source_job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('destination_job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['source_job_id', 'destination_job_id']);
            $table->index(['destination_job_id', 'source_job_id']);
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('merged_to_id');
        });

        Schema::table('job_notes', function (Blueprint $table) {
            $table->boolean('mergeable')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merged_jobs');

        Schema::table('jobs', function (Blueprint $table) {
            $table->bigInteger('merged_to_id')->nullable();

            $table->foreign('merged_to_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('job_notes', function (Blueprint $table) {
            $table->dropColumn('mergeable');
        });
    }
}
