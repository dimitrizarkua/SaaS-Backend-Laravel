<?php

use Illuminate\Database\Seeder;
use App\Components\Jobs\Enums\JobTaskTypes;

/**
 * Class OperationsSeeder
 */
class OperationsSeeder extends Seeder
{
    private $jobTaskTypes = [
        [JobTaskTypes::FIRST_ATTENDANCE, true, 120, 0, false, 16711680, false],
        [JobTaskTypes::SECOND_ATTENDANCE, true, 120, 0, false, 65280, false],
        [JobTaskTypes::ON_GOING_ATTENDANCE, true, 120, 0, false, 255, false],
        [JobTaskTypes::EQUIPMENT_PICKUP, true, 60, 0, false, 16776960, false],
        [JobTaskTypes::FINAL_ATTENDANCE, true, 60, 0, false, 16711935, false],
        [JobTaskTypes::INITIAL_CONTACT_KPI, 0, 0, 24, false, 65535, true],
        [JobTaskTypes::VAN_CLEAN, true, 0, 0, false, 8421504, false],
    ];

    private $vehicleStatusTypes = [
        ['Available', false, true],
        ['In-Use', true, false],
        ['Out of Service (Servicing & Repairs)', true, false],
        ['Out of Service (Cleaning)', true, false],
        ['Reserved', true, false],
    ];

    /**
     * Seed the contact types, categories and statuses.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->jobTaskTypes as $type) {
            DB::table('job_task_types')->insert([
                'name'                     => $type[0],
                'can_be_scheduled'         => $type[1],
                'allow_edit_due_date'      => true,
                'default_duration_minutes' => $type[2],
                'kpi_hours'                => $type[3],
                'kpi_include_afterhours'   => $type[4],
                'color'                    => $type[5],
                'auto_create'              => $type[6],
            ]);
        }

        foreach ($this->vehicleStatusTypes as $type) {
            DB::table('vehicle_status_types')->insert([
                'name'                      => $type[0],
                'makes_vehicle_unavailable' => $type[1],
                'is_default'                => $type[2],
            ]);
        }
    }
}
