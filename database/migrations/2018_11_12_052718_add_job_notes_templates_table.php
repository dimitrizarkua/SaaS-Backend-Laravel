<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class AddJobNotesTemplatesTable
 */
class AddJobNotesTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_notes_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->text('body');
            $table->boolean('active')->default(true);

            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_notes_templates');
    }
}
