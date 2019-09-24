<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditCardTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cc_transactions', function (Blueprint $table) {
            $table->bigInteger('payment_id');
            $table->decimal('amount', 10, 2);
            $table->text('external_transaction_id');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('settled_at');

            $table->primary('payment_id');

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
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
        Schema::dropIfExists('cc_transactions');
    }
}
