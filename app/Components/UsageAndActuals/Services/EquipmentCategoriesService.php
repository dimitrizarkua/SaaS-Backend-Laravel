<?php

namespace App\Components\UsageAndActuals\Services;

use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Interfaces\EquipmentCategoriesInterface;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use App\Components\UsageAndActuals\Models\VO\EquipmentCategoryData;
use Illuminate\Support\Facades\DB;

/**
 * Class EquipmentCategoriesService
 *
 * @package App\Components\UsageAndActuals\Services
 */
class EquipmentCategoriesService implements EquipmentCategoriesInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createEquipmentCategory(EquipmentCategoryData $data): EquipmentCategory
    {
        return DB::transaction(function () use ($data) {
            $equipmentCategory = EquipmentCategory::create($data->toArray());

            EquipmentCategoryChargingInterval::insert([
                'equipment_category_id'          => $equipmentCategory->id,
                'charging_interval'              => $data->getChargingInterval(),
                'charging_rate_per_interval'     => $data->getChargingRatePerInterval(),
                'max_count_to_the_next_interval' => $data->getMaxCountToTheNextInterval(),
                'is_default'                     => true,
            ]);

            if (EquipmentCategoryChargingIntervals::DAY === $data->getChargingInterval()
                && null !== $data->getChargeRatePerWeek()
                && null !== $data->getMaxCountToTheNextInterval()
            ) {
                EquipmentCategoryChargingInterval::insert([
                    'equipment_category_id'          => $equipmentCategory->id,
                    'charging_interval'              => EquipmentCategoryChargingIntervals::WEEK,
                    'charging_rate_per_interval'     => $data->getChargeRatePerWeek(),
                    'max_count_to_the_next_interval' => 0,
                    'is_default'                     => true,
                ]);
            }

            return $equipmentCategory;
        });
    }
}
