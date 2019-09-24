<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Class ChangeJobLabours
 */
class ChangeJobLabours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $records = DB::query()
            ->select(['id', 'break'])
            ->from('job_labours')
            ->get();

        Schema::table('job_labours', function (Blueprint $table) {
                $table->dropColumn(['break']);
        });

        Schema::table('job_labours', function (Blueprint $table) {
            $table->integer('break')->nullable();
            $table->integer('first_tier_time_amount')->default(0);
            $table->integer('second_tier_time_amount')->default(0);
            $table->integer('third_tier_time_amount')->default(0);
            $table->integer('fourth_tier_time_amount')->default(0);
        });

        $records->each(function ($record) {
            $breakInNumberOfMinutes = Carbon::now()->startOfDay()->diffInMinutes($record->break);
            DB::query()
                ->from('job_labours')
                ->where('id', $record->id)
                ->update([
                    'break' => $breakInNumberOfMinutes,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $records = DB::query()
            ->from('job_labours')
            ->select(['id', 'break'])
            ->get();

        Schema::table('job_labours', function (Blueprint $table) {
            $table->dropColumn(['break']);
            $table->dropColumn(['first_tier_time_amount']);
            $table->dropColumn(['second_tier_time_amount']);
            $table->dropColumn(['third_tier_time_amount']);
            $table->dropColumn(['fourth_tier_time_amount']);
        });

        Schema::table('job_labours', function (Blueprint $table) {
            $table->time('break')->nullable();
        });

        $records->each(function ($record) {
            $hours   = (int)($record->break / 60);
            $minutes = $record->break % 60;

            $breakInTime = Carbon::now();
            $breakInTime->hour($hours);
            $breakInTime->minute($minutes);
            $breakInTime->second(0);
            
            DB::query()
                ->from('job_labours')
                ->where('id', $record->id)
                ->update([
                    'break' => $breakInTime->format('H:i:s'),
                ]);
        });
    }
}
