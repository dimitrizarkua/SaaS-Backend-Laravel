<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateForwardedPaymentTable
 */
class CreateForwardedPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forwarded_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('payment_id');
            $table->text('remittance_reference');
            $table->timestamp('transferred_at')->nullable();

            $table->index('payment_id');

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('forwarded_payment_invoice', function (Blueprint $table) {
            $table->bigInteger('forwarded_payment_id');
            $table->bigInteger('invoice_id');
            $table->primary(['forwarded_payment_id', 'invoice_id']);
            $table->decimal('amount', 10, 2);

            $table->index('forwarded_payment_id');
            $table->foreign('forwarded_payment_id')
                ->references('id')
                ->on('forwarded_payments')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('invoice_id');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
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
        Schema::dropIfExists('forwarded_payment_invoice');
        Schema::dropIfExists('forwarded_payments');
    }
}
