<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class FinanceDataImports
 */
class FinanceDataImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->text('code')->nullable(true)->change();
            $table->text('export_code')->nullable();
            $table->boolean('is_special')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->text('code')->nullable(false)->change();
            $table->dropColumn('export_code');
            $table->dropColumn('is_special');
        });
    }
}
