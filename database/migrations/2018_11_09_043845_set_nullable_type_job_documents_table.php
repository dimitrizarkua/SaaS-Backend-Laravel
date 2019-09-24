<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class SetNullableTypeJobDocumentsTable
 */
class SetNullableTypeJobDocumentsTable extends Migration
{
    /**
     * Set type to null.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_documents', function (Blueprint $table) {
            $table->text('type')->nullable()->change();
        });
    }

    /**
     * Reverse type to null.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_documents', function (Blueprint $table) {
            $table->text('type')->nullable(false)->change();
        });
    }
}
