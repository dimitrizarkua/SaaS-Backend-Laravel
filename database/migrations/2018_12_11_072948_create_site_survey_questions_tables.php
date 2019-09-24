<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteSurveyQuestionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createSiteSurveyQuestionsTable();
        $this->createSiteSurveyQuestionOptionsTable();
        $this->createJobSiteSurveyQuestionsTable();
    }

    private function createSiteSurveyQuestionsTable()
    {
        Schema::create('site_survey_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->boolean('is_active')->default(true);
        });
    }

    private function createSiteSurveyQuestionOptionsTable()
    {
        Schema::create('site_survey_question_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('site_survey_question_id');
            $table->text('name');
            $table->unique([
                'site_survey_question_id',
                'name',
            ]);

            $table->index('site_survey_question_id');
            $table->foreign('site_survey_question_id')
                ->references('id')
                ->on('site_survey_questions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    private function createJobSiteSurveyQuestionsTable()
    {
        Schema::create('job_site_survey_questions', function (Blueprint $table) {
            $table->bigInteger('job_id');
            $table->bigInteger('site_survey_question_id');
            $table->primary(['job_id', 'site_survey_question_id']);
            $table->bigInteger('site_survey_question_option_id')->nullable();
            $table->text('answer')->nullable();

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('site_survey_question_id');
            $table->foreign('site_survey_question_id')
                ->references('id')
                ->on('site_survey_questions')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('site_survey_question_option_id');
            $table->foreign('site_survey_question_option_id')
                ->references('id')
                ->on('site_survey_question_options')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_site_survey_questions');
        Schema::dropIfExists('site_survey_question_options');
        Schema::dropIfExists('site_survey_questions');
    }
}
