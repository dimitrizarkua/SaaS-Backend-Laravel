<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddCreatedAtFieldForManagedAccount
 */
class AddCreatedAtFieldForManagedAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('managed_accounts', function (Blueprint $table) {
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('managed_accounts', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_at');
        });
    }
}
