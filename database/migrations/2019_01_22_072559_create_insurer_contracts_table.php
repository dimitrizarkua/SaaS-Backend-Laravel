<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInsurerContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurer_contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('contact_id');
            $table->text('contract_number');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->date('effect_date');
            $table->date('termination_date')->nullable();

            $table->index('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->bigInteger('insurer_contract_id')
                ->nullable();

            $table->index('insurer_contract_id');
            $table->foreign('insurer_contract_id')
                ->references('id')
                ->on('insurer_contracts')
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
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('insurer_contract_id');
        });

        Schema::dropIfExists('insurer_contracts');
    }
}
