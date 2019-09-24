<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLimitsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('invoice_approve_limit', 10, 2)->default(0);
            $table->decimal('purchase_order_approve_limit', 10, 2)->default(0);
            $table->decimal('credit_note_approval_limit', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('invoice_approve_limit');
            $table->dropColumn('purchase_order_approve_limit');
            $table->dropColumn('credit_note_approval_limit');
        });
    }
}
