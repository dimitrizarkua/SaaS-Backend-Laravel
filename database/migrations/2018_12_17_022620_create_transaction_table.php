<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('accounting_organization_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index('accounting_organization_id');
            $table->foreign('accounting_organization_id')
                ->references('id')
                ->on('accounting_organizations')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('transaction_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('transaction_id');
            $table->bigInteger('gl_account_id');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_debit');

            $table->index('transaction_id');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('gl_account_id');
            $table->foreign('gl_account_id')
                ->references('id')
                ->on('gl_accounts')
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
        Schema::dropIfExists('transaction_records');
        Schema::dropIfExists('transactions');
    }
}
