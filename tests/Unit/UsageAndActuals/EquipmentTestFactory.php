<?php

namespace Tests\Unit\UsageAndActuals;

use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobEquipmentChargingInterval;
use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use App\Components\UsageAndActuals\Models\EquipmentCategoryInsurerContract;
use Faker\Factory;

/**
 * Class EquipmentTestFactory
 *
 * @package Tests\Unit\UsageAndActuals
 */
class EquipmentTestFactory
{
    /**
     * @param string|null $interval
     *
     * @return Equipment
     */
    public static function createEquipmentWithInterval(string $interval = null): Equipment
    {
        $faker = Factory::create();
        if (null === $interval) {
            $interval = $faker->randomElement(EquipmentCategoryChargingIntervals::values());
        }
        /** @var EquipmentCategoryChargingInterval $chargingInterval */
        $chargingInterval = factory(EquipmentCategoryChargingInterval::class)->create([
            'charging_interval' => $interval,
        ]);
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create([
            'equipment_category_id' => $chargingInterval->equipment_category_id,
        ]);

        return $equipment;
    }

    /**
     * @return Equipment
     */
    public static function createEquipmentWithDayAndWeekIntervals(): Equipment
    {
        /** @var EquipmentCategoryChargingInterval $dayInterval */
        $dayInterval = factory(EquipmentCategoryChargingInterval::class)->create([
            'charging_interval' => EquipmentCategoryChargingIntervals::DAY,
        ]);
        factory(EquipmentCategoryChargingInterval::class)->create([
            'equipment_category_id'          => $dayInterval->equipment_category_id,
            'charging_interval'              => EquipmentCategoryChargingIntervals::WEEK,
            'max_count_to_the_next_interval' => 0,
        ]);
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create([
            'equipment_category_id' => $dayInterval->equipment_category_id,
        ]);

        return $equipment;
    }

    /**
     * @param int $insurerContractId
     *
     * @return Equipment
     */
    public static function createEquipmentWithDefaultAndInsurerContractIntervals(int $insurerContractId): Equipment
    {
        /** @var EquipmentCategoryChargingInterval $defaultInterval */
        $defaultInterval = factory(EquipmentCategoryChargingInterval::class)->create();
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create([
            'equipment_category_id' => $defaultInterval->equipment_category_id,
        ]);
        /** @var EquipmentCategoryChargingInterval $insurerContractInterval */
        $insurerContractInterval = factory(EquipmentCategoryChargingInterval::class)->create([
            'equipment_category_id' => $defaultInterval->equipment_category_id,
            'is_default'            => false,
        ]);
        factory(EquipmentCategoryInsurerContract::class)->create([
            'insurer_contract_id'                     => $insurerContractId,
            'equipment_category_charging_interval_id' => $insurerContractInterval->id,
        ]);

        return $equipment;
    }

    /**
     * @param int   $jobId
     * @param array $data
     * @param bool  $withDayAndWeekIntervals
     *
     * @return JobEquipment
     */
    public static function createJobEquipmentWithInterval(
        int $jobId,
        array $data = [],
        bool $withDayAndWeekIntervals = false
    ): JobEquipment {
        $faker = Factory::create();
        if (!$withDayAndWeekIntervals) {
            /** @var EquipmentCategoryChargingInterval $interval */
            $interval = factory(EquipmentCategoryChargingInterval::class)->create();
            /** @var JobEquipment $jobEquipment */
            $jobEquipment = factory(JobEquipment::class)->create([
                'job_id'                   => $jobId,
                'interval'                 => $interval->charging_interval,
                'equipment_id'             => $data['equipment_id'] ?? factory(Equipment::class)->create()->id,
                'invoice_item_id'          => $data['invoice_item_id'] ?? null,
                'intervals_count_override' => $data['intervals_count'] ?? $faker->numberBetween(1, 9),
            ]);
            factory(JobEquipmentChargingInterval::class)->create([
                'job_equipment_id'                        => $jobEquipment->id,
                'equipment_category_charging_interval_id' => $interval->id,
                'charging_interval'                       => $interval->charging_interval,
                'charging_rate_per_interval'              =>
                    $data['charging_rate_per_interval'] ?? $faker->randomFloat(2, 1, 100),
                'max_count_to_the_next_interval'          => $data['max_count_to_the_next_interval'] ?? 0,
                'up_to_amount'                            => $data['up_to_amount'] ?? null,
                'up_to_interval_count'                    => $data['up_to_interval_count'] ?? null,
            ]);
        } else {
            /** @var EquipmentCategoryChargingInterval $dayInterval */
            $dayInterval = factory(EquipmentCategoryChargingInterval::class)->create([
                'charging_interval' => EquipmentCategoryChargingIntervals::DAY,
            ]);
            /** @var EquipmentCategoryChargingInterval $dayInterval */
            $weekInterval = factory(EquipmentCategoryChargingInterval::class)->create([
                'charging_interval' => EquipmentCategoryChargingIntervals::WEEK,
            ]);
            /** @var JobEquipment $jobEquipment */
            $jobEquipment = factory(JobEquipment::class)->create([
                'job_id'                   => $jobId,
                'interval'                 => $dayInterval->charging_interval,
                'intervals_count_override' => $data['intervals_count'] ?? $faker->numberBetween(4, 7),
            ]);
            factory(JobEquipmentChargingInterval::class)->create([
                'job_equipment_id'                        => $jobEquipment->id,
                'equipment_category_charging_interval_id' => $dayInterval->id,
                'charging_interval'                       => $dayInterval->charging_interval,
                'max_count_to_the_next_interval'          =>
                    $data['max_count_to_the_next_interval'] ?? $jobEquipment->intervals_count_override - 1,
                'up_to_amount'                            => $data['up_to_amount'] ?? null,
                'up_to_interval_count'                    => $data['up_to_interval_count'] ?? null,
            ]);
            factory(JobEquipmentChargingInterval::class)->create([
                'job_equipment_id'                        => $jobEquipment->id,
                'equipment_category_charging_interval_id' => $weekInterval->id,
                'charging_interval'                       => $weekInterval->charging_interval,
                'charging_rate_per_interval'              =>
                    $data['charging_rate_per_interval'] ?? $faker->randomFloat(2, 1, 100),
                'max_count_to_the_next_interval'          => 0,
                'up_to_amount'                            => $data['up_to_amount'] ?? null,
                'up_to_interval_count'                    => $data['up_to_interval_count'] ?? null,
            ]);
        }

        return $jobEquipment->load('chargingIntervals');
    }
}
