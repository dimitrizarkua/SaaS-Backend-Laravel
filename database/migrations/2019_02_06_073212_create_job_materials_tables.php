<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobMaterialsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('measure_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code');
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->bigInteger('measure_unit_id');
            $table->decimal('default_sell_cost_per_unit', 10, 2);
            $table->decimal('default_buy_cost_per_unit', 10, 2);
            $table->timestamps();

            $table->index('measure_unit_id');
            $table->foreign('measure_unit_id')
                ->references('id')
                ->on('measure_units')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('job_material', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('material_id');
            $table->bigInteger('creator_id')->nullable();
            $table->timestamp('used_at');
            $table->decimal('sell_cost_per_unit', 10, 2)->nullable();
            $table->decimal('buy_cost_per_unit', 10, 2);
            $table->integer('quantity_used');
            $table->integer('quantity_used_override')->nullable();
            $table->bigInteger('invoice_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('material_id');
            $table->foreign('material_id')
                ->references('id')
                ->on('materials')
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

        Schema::create('insurer_contract_material', function (Blueprint $table) {
            $table->bigInteger('insurer_contract_id');
            $table->bigInteger('material_id');
            $table->text('name')->nullable();
            $table->decimal('sell_cost_per_unit', 10, 2);
            $table->integer('up_to_units')->nullable();
            $table->decimal('up_to_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->primary(['insurer_contract_id', 'material_id']);

            $table->foreign('insurer_contract_id')
                ->references('id')
                ->on('insurer_contracts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('material_id')
                ->references('id')
                ->on('materials')
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
        Schema::dropIfExists('insurer_contract_material');
        Schema::dropIfExists('job_material');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('measure_units');
    }
}
