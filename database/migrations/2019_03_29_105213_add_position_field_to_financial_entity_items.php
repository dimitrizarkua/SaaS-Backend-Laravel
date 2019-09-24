<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddPositionFieldToFinancialEntityItems
 */
class AddPositionFieldToFinancialEntityItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('position')->default(1);
        });
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->integer('position')->default(1);
        });
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->integer('position')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('position');
        });
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->dropColumn('position');
        });
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
}
