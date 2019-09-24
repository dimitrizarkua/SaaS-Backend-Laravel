<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class FixJobEquipmentTable
 */
class FixJobEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_equipment', function (Blueprint $table) {
            $table->renameColumn('used_at', 'started_at');
            $table->datetime('ended_at')->nullable();
            $table->integer('intervals_count')->nullable()->default(null)->change();
            $table->integer('intervals_count_override')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_equipment', function (Blueprint $table) {
            $table->renameColumn('started_at', 'used_at');
            $table->dropColumn('ended_at');
            $table->integer('intervals_count')->nullable(false)->default(0)->change();
            $table->integer('intervals_count_override')->nullable(false)->default(0)->change();
        });
    }
}
