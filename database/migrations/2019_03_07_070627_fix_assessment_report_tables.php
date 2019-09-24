<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class FixAssessmentReportTables
 */
class FixAssessmentReportTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assessment_reports', function (Blueprint $table) {
            $table->bigInteger('document_id')->nullable();

            $table->index('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('assessment_report_costing_stages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_id');
            $table->text('name');
            $table->integer('position');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('assessment_report_id');
            $table->foreign('assessment_report_id')
                ->references('id')
                ->on('assessment_reports')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('assessment_report_cost_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assessment_report_costing_stage_id');
            $table->bigInteger('assessment_report_id');
            $table->bigInteger('gs_code_id');
            $table->integer('position');
            $table->text('description');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('markup', 10, 2)->default(0);
            $table->bigInteger('tax_rate_id');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));


            $table->index('gs_code_id');
            $table->foreign('gs_code_id')
                ->references('id')
                ->on('gs_codes')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('assessment_report_costing_stage_id');
            $table->foreign('assessment_report_costing_stage_id')
                ->references('id')
                ->on('assessment_report_costing_stages')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('assessment_report_id');
            $table->foreign('assessment_report_id')
                ->references('id')
                ->on('assessment_reports')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('tax_rate_id');
            $table->foreign('tax_rate_id')
                ->references('id')
                ->on('tax_rates')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::table('assessment_report_section_cost_items', function (Blueprint $table) {
            $table->bigInteger('assessment_report_cost_item_id');
            $table->dropColumn('id');
            $table->dropColumn('name');
            $table->dropColumn('cost_per_unit');
            $table->dropColumn('quantity_used');
            $table->dropColumn('tax_rate_id');

            $table->primary([
                'assessment_report_section_id',
                'assessment_report_cost_item_id',
            ]);

            $table->index('assessment_report_cost_item_id');
            $table->foreign('assessment_report_cost_item_id')
                ->references('id')
                ->on('assessment_report_cost_items')
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
        Schema::table('assessment_report_section_cost_items', function (Blueprint $table) {
            $table->dropPrimary([
                'assessment_report_section_id',
                'assessment_report_cost_item_id',
            ]);
        });
        Schema::table('assessment_report_section_cost_items', function (Blueprint $table) {
            $table->dropColumn('assessment_report_cost_item_id');
            $table->bigIncrements('id');
            $table->text('name');
            $table->decimal('cost_per_unit', 10, 2);
            $table->integer('quantity_used');
            $table->bigInteger('tax_rate_id');

            $table->index('tax_rate_id');
            $table->foreign('tax_rate_id')
                ->references('id')
                ->on('tax_rates')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::dropIfExists('assessment_report_cost_items');
        Schema::dropIfExists('assessment_report_costing_stages');
        Schema::table('assessment_reports', function (Blueprint $table) {
            $table->dropColumn('document_id');
        });
    }
}
