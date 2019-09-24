<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaboursTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labour_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->decimal('first_tier_hourly_rate', 10, 2);
            $table->decimal('second_tier_hourly_rate', 10, 2);
            $table->decimal('third_tier_hourly_rate', 10, 2);
            $table->decimal('fourth_tier_hourly_rate', 10, 2);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('allowance_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('location_id');
            $table->string('name')->unique();
            $table->decimal('charge_rate_per_interval', 10, 2);
            $table->string('charging_interval');
            $table->timestamps();

            $table->index('location_id');
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('laha_compensations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('rate_per_day', 10, 2);
            $table->timestamps();
        });

        Schema::create('job_laha_compensation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('user_id');
            $table->bigInteger('creator_id');
            $table->bigInteger('laha_compensation_id');
            $table->date('date_started');
            $table->decimal('rate_per_day', 10, 2);
            $table->integer('days');
            $table->timestamps();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('approver_id')->nullable();

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

            $table->index('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('laha_compensation_id');
            $table->foreign('laha_compensation_id')
                ->references('id')
                ->on('laha_compensations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('approver_id');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('job_allowances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('user_id');
            $table->bigInteger('creator_id');
            $table->bigInteger('allowance_type_id');
            $table->date('date_given');
            $table->decimal('charge_rate_per_interval', 10, 2);
            $table->integer('amount');
            $table->timestamps();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('approver_id')->nullable();

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

            $table->index('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('allowance_type_id');
            $table->foreign('allowance_type_id')
                ->references('id')
                ->on('allowance_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('approver_id');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('job_reimbursements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('user_id');
            $table->bigInteger('creator_id');
            $table->date('date_of_expense');
            $table->bigInteger('document_id');
            $table->string('description');
            $table->decimal('total_amount', 10, 2);
            $table->boolean('is_chargeable');
            $table->bigInteger('invoice_item_id')->nullable();
            $table->timestamps();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('approver_id')->nullable();

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

            $table->index('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('invoice_item_id');
            $table->foreign('invoice_item_id')
                ->references('id')
                ->on('invoice_items')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('approver_id');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('insurer_contract_labour_types', function (Blueprint $table) {
            $table->bigInteger('insurer_contract_id');
            $table->bigInteger('labour_type_id');
            $table->text('name')->nullable();
            $table->decimal('first_tier_hourly_rate', 10, 2);
            $table->decimal('second_tier_hourly_rate', 10, 2);
            $table->decimal('third_tier_hourly_rate', 10, 2);
            $table->decimal('fourth_tier_hourly_rate', 10, 2);
            $table->integer('up_to_hours')->nullable();
            $table->decimal('up_to_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->primary(['insurer_contract_id', 'labour_type_id']);

            $table->foreign('insurer_contract_id')
                ->references('id')
                ->on('insurer_contracts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('labour_type_id')
                ->references('id')
                ->on('labour_types')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::create('job_labours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('labour_type_id');
            $table->bigInteger('worker_id');
            $table->bigInteger('creator_id');
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->timestamp('started_at_override');
            $table->timestamp('ended_at_override');
            $table->time('break')->nullable();
            $table->decimal('first_tier_hourly_rate', 10, 2);
            $table->decimal('second_tier_hourly_rate', 10, 2);
            $table->decimal('third_tier_hourly_rate', 10, 2);
            $table->decimal('fourth_tier_hourly_rate', 10, 2);
            $table->decimal('calculated_total_amount', 10, 2);
            $table->bigInteger('invoice_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('labour_type_id');
            $table->foreign('labour_type_id')
                ->references('id')
                ->on('labour_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('worker_id');
            $table->foreign('worker_id')
                ->references('id')
                ->on('users')
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


        Schema::create('holidays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('job_laha_compensation');
        Schema::dropIfExists('laha_compensations');
        Schema::dropIfExists('job_allowances');
        Schema::dropIfExists('allowance_types');
        Schema::dropIfExists('job_reimbursements');
        Schema::dropIfExists('insurer_contract_labour_types');
        Schema::dropIfExists('job_labours');
        Schema::dropIfExists('labour_types');
    }
}
