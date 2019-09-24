<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Components\Jobs\Models\JobTask;
use Illuminate\Support\Carbon;

class FixJobTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_tasks', function (Blueprint $table) {
            $table->dateTime('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->nullable(true);
            $table->dateTime('kpi_missed_at')->nullable();

            $table->index('kpi_missed_at');
        });

        Schema::table('job_task_types', function (Blueprint $table) {
            $table->boolean('auto_create')->default(false);
        });

        $tasks = JobTask::all();
        foreach ($tasks as $task) {
            $task->created_at = $task->starts_at ?? Carbon::now();
            $task->saveOrFail();
        }

        Schema::table('job_tasks', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_task_types', function (Blueprint $table) {
            $table->dropColumn('auto_create');
        });

        Schema::table('job_tasks', function (Blueprint $table) {
            $table->dropIndex(['kpi_missed_at']);
            $table->dropColumn('created_at');
            $table->dropColumn('kpi_missed_at');
        });
    }
}
