<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddEquipmentTables
 */
class AddEquipmentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createEquipmentCategoriesTable();
        $this->createEquipmentCategoryChargingIntervalsTable();
        $this->createEquipmentCategoryInsurerContractTable();
        $this->createEquipmentTable();
        $this->createJobEquipmentTable();
        $this->createJobEquipmentChargingIntervalTable();
        $this->createEquipmentNoteTable();
    }

    private function createEquipmentCategoriesTable()
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
            $table->boolean('is_airmover')->default(false);
            $table->boolean('is_dehum')->default(false);
            $table->decimal('default_buy_cost_per_interval', 10, 2);
        });
    }

    private function createEquipmentCategoryChargingIntervalsTable()
    {
        Schema::create('equipment_category_charging_intervals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('equipment_category_id');
            $table->text('charging_interval');
            $table->decimal('charging_rate_per_interval', 10, 2);
            $table->integer('max_count_to_the_next_interval')->default(0);
            $table->boolean('is_default')->default(false);
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('equipment_category_id');
            $table->foreign('equipment_category_id')
                ->references('id')
                ->on('equipment_categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    private function createEquipmentCategoryInsurerContractTable()
    {
        Schema::create('equipment_category_insurer_contract', function (Blueprint $table) {
            $table->bigInteger('insurer_contract_id');
            $table->bigInteger('equipment_category_charging_interval_id');
            $table->text('name')->nullable();
            $table->decimal('up_to_amount', 10, 2)->nullable();
            $table->integer('up_to_interval_count')->nullable();
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->primary([
                'insurer_contract_id',
                'equipment_category_charging_interval_id',
            ]);

            $table->index('insurer_contract_id');
            $table->foreign('insurer_contract_id')
                ->references('id')
                ->on('insurer_contracts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('equipment_category_charging_interval_id');
            $table->foreign('equipment_category_charging_interval_id')
                ->references('id')
                ->on('equipment_category_charging_intervals')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createEquipmentTable()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('barcode');
            $table->bigInteger('equipment_category_id');
            $table->bigInteger('location_id')->nullable();
            $table->text('make');
            $table->text('model');
            $table->text('serial_number');
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
            $table->datetime('last_test_tag_at')->nullable();

            $table->index('equipment_category_id');
            $table->foreign('equipment_category_id')
                ->references('id')
                ->on('equipment_categories')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('location_id');
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createJobEquipmentTable()
    {
        Schema::create('job_equipment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('equipment_id');
            $table->bigInteger('creator_id');
            $table->datetime('used_at');
            $table->text('interval');
            $table->integer('intervals_count')->default(0);
            $table->integer('intervals_count_override')->default(0);
            $table->decimal('buy_cost_per_interval', 10, 2);
            $table->bigInteger('invoice_item_id')->nullable();
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('equipment_id');
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('invoice_item_id');
            $table->foreign('invoice_item_id')
                ->references('id')
                ->on('invoice_items')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createJobEquipmentChargingIntervalTable()
    {
        Schema::create('job_equipment_charging_interval', function (Blueprint $table) {
            $table->bigInteger('job_equipment_id');
            $table->bigInteger('equipment_category_charging_interval_id');
            $table->text('charging_interval');
            $table->decimal('charging_rate_per_interval', 10, 2);
            $table->integer('max_count_to_the_next_interval')->default(0);
            $table->decimal('up_to_amount', 10, 2)->nullable();
            $table->integer('up_to_interval_count')->nullable();

            $table->primary([
                'job_equipment_id',
                'equipment_category_charging_interval_id',
            ]);

            $table->index('job_equipment_id');
            $table->foreign('job_equipment_id')
                ->references('id')
                ->on('job_equipment')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('equipment_category_charging_interval_id');
            $table->foreign('equipment_category_charging_interval_id')
                ->references('id')
                ->on('equipment_category_charging_intervals')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createEquipmentNoteTable()
    {
        Schema::create('equipment_note', function (Blueprint $table) {
            $table->bigInteger('equipment_id');
            $table->bigInteger('note_id');

            $table->primary([
                'equipment_id',
                'note_id',
            ]);

            $table->index('equipment_id');
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('note_id');
            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
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
        Schema::dropIfExists('equipment_note');
        Schema::dropIfExists('job_equipment_charging_interval');
        Schema::dropIfExists('job_equipment');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('equipment_category_insurer_contract');
        Schema::dropIfExists('equipment_category_charging_intervals');
        Schema::dropIfExists('equipment_categories');
    }
}
