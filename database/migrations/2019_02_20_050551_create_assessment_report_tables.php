<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateAssessmentReportTables
 */
class CreateAssessmentReportTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createAssessmentReportsTable();
        $this->createAssessmentReportStatusesTable();
        $this->createFlooringSubtypesTable();
        $this->createUnderlayTypesTable();
        $this->createNonRestorableReasonsTable();
        $this->createCarpetTypesTable();
        $this->createCarpetConstructionTypesTable();
        $this->createCarpetAgesTable();
        $this->createCarpetFaceFibresTable();
        $this->createAssessmentReportSectionsTable();
        $this->createAssessmentReportSectionTextBlocksTable();
        $this->createAssessmentReportSectionImagesTable();
        $this->createAssessmentReportSectionPhotosTable();
        $this->createAssessmentReportSectionCostItemsTable();
        $this->createAssessmentReportSectionRoomsTable();
    }

    private function createAssessmentReportsTable()
    {
        Schema::create('assessment_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('user_id');
            $table->text('heading')->nullable();
            $table->text('subheading')->nullable();
            $table->date('date');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createAssessmentReportStatusesTable()
    {
        Schema::create('assessment_report_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_id');
            $table->bigInteger('user_id')->nullable();
            $table->text('status');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_id');
            $table->foreign('assessment_report_id')
                ->references('id')
                ->on('assessment_reports')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createFlooringSubtypesTable()
    {
        Schema::create('flooring_subtypes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('flooring_type_id');
            $table->text('name')->unique();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->index('flooring_type_id');
            $table->foreign('flooring_type_id')
                ->references('id')
                ->on('flooring_types')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    private function createUnderlayTypesTable()
    {
        Schema::create('underlay_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    private function createNonRestorableReasonsTable()
    {
        Schema::create('non_restorable_reasons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    private function createCarpetTypesTable()
    {
        Schema::create('carpet_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    private function createCarpetConstructionTypesTable()
    {
        Schema::create('carpet_construction_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    private function createCarpetAgesTable()
    {
        Schema::create('carpet_ages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    private function createCarpetFaceFibresTable()
    {
        Schema::create('carpet_face_fibres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    private function createAssessmentReportSectionsTable()
    {
        Schema::create('assessment_report_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_id');
            $table->text('type');
            $table->integer('position');
            $table->text('heading')->nullable();
            $table->text('heading_style')->nullable();
            $table->integer('heading_color')->nullable();
            $table->text('text')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_id');
            $table->foreign('assessment_report_id')
                ->references('id')
                ->on('assessment_reports')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createAssessmentReportSectionTextBlocksTable()
    {
        Schema::create('assessment_report_section_text_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_section_id');
            $table->integer('position');
            $table->text('text')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_section_id');
            $table->foreign('assessment_report_section_id')
                ->references('id')
                ->on('assessment_report_sections')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createAssessmentReportSectionImagesTable()
    {
        Schema::create('assessment_report_section_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_section_id');
            $table->bigInteger('photo_id')->nullable();
            $table->text('caption')->nullable();
            $table->integer('desired_width');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_section_id');
            $table->foreign('assessment_report_section_id')
                ->references('id')
                ->on('assessment_report_sections')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('photo_id');
            $table->foreign('photo_id')
                ->references('id')
                ->on('photos')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createAssessmentReportSectionPhotosTable()
    {
        Schema::create('assessment_report_section_photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_section_id');
            $table->bigInteger('photo_id')->nullable();
            $table->integer('position');
            $table->text('caption')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_section_id');
            $table->foreign('assessment_report_section_id')
                ->references('id')
                ->on('assessment_report_sections')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('photo_id');
            $table->foreign('photo_id')
                ->references('id')
                ->on('photos')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createAssessmentReportSectionCostItemsTable()
    {
        Schema::create('assessment_report_section_cost_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_section_id');
            $table->integer('position');
            $table->text('name');
            $table->decimal('cost_per_unit', 10, 2);
            $table->integer('quantity_used');
            $table->bigInteger('tax_rate_id');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_section_id');
            $table->foreign('assessment_report_section_id')
                ->references('id')
                ->on('assessment_report_sections')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('tax_rate_id');
            $table->foreign('tax_rate_id')
                ->references('id')
                ->on('tax_rates')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createAssessmentReportSectionRoomsTable()
    {
        Schema::create('assessment_report_section_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_section_id');
            $table->text('name');
            $table->bigInteger('flooring_type_id')->nullable();
            $table->bigInteger('flooring_subtype_id')->nullable();
            $table->decimal('dimensions_length', 6, 2)->nullable();
            $table->decimal('dimensions_width', 6, 2)->nullable();
            $table->decimal('dimensions_height', 6, 2)->nullable();
            $table->decimal('dimensions_affected_length', 6, 2)->nullable();
            $table->decimal('dimensions_affected_width', 6, 2)->nullable();
            $table->boolean('underlay_required')->default(false);
            $table->bigInteger('underlay_type_id')->nullable();
            $table->text('underlay_type_note')->nullable();
            $table->decimal('dimensions_underlay_length', 6, 2)->nullable();
            $table->decimal('dimensions_underlay_width', 6, 2)->nullable();
            $table->boolean('trims_required')->default(false);
            $table->text('trim_type')->nullable();
            $table->boolean('restorable')->default(false);
            $table->bigInteger('non_restorable_reason_id')->nullable();
            $table->bigInteger('carpet_type_id')->nullable();
            $table->bigInteger('carpet_construction_type_id')->nullable();
            $table->bigInteger('carpet_age_id')->nullable();
            $table->bigInteger('carpet_face_fibre_id')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_section_id');
            $table->foreign('assessment_report_section_id')
                ->references('id')
                ->on('assessment_report_sections')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('flooring_type_id');
            $table->foreign('flooring_type_id')
                ->references('id')
                ->on('flooring_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('flooring_subtype_id');
            $table->foreign('flooring_subtype_id')
                ->references('id')
                ->on('flooring_subtypes')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('underlay_type_id');
            $table->foreign('underlay_type_id')
                ->references('id')
                ->on('underlay_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('non_restorable_reason_id');
            $table->foreign('non_restorable_reason_id')
                ->references('id')
                ->on('non_restorable_reasons')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('carpet_type_id');
            $table->foreign('carpet_type_id')
                ->references('id')
                ->on('carpet_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('carpet_construction_type_id');
            $table->foreign('carpet_construction_type_id')
                ->references('id')
                ->on('carpet_construction_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('carpet_age_id');
            $table->foreign('carpet_age_id')
                ->references('id')
                ->on('carpet_ages')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('carpet_face_fibre_id');
            $table->foreign('carpet_face_fibre_id')
                ->references('id')
                ->on('carpet_face_fibres')
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
        Schema::dropIfExists('assessment_report_section_rooms');
        Schema::dropIfExists('assessment_report_section_cost_items');
        Schema::dropIfExists('assessment_report_section_photos');
        Schema::dropIfExists('assessment_report_section_images');
        Schema::dropIfExists('assessment_report_section_text_blocks');
        Schema::dropIfExists('assessment_report_sections');
        Schema::dropIfExists('carpet_face_fibres');
        Schema::dropIfExists('carpet_ages');
        Schema::dropIfExists('carpet_construction_types');
        Schema::dropIfExists('carpet_types');
        Schema::dropIfExists('non_restorable_reasons');
        Schema::dropIfExists('underlay_types');
        Schema::dropIfExists('flooring_subtypes');
        Schema::dropIfExists('assessment_report_statuses');
        Schema::dropIfExists('assessment_reports');
    }
}
