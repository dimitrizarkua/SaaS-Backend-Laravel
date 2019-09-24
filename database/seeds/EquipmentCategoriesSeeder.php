<?php

use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 * Class EquipmentCategoriesSeeder
 */
class EquipmentCategoriesSeeder extends Seeder
{
    /**
     * @param $str
     *
     * @return float
     */
    private function parseFloatFromCurrencyString($str): float
    {
        $str = str_replace(',', '.', $str);

        return (float)filter_var($str, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Safely seeds equipment_categories table (doesn't fail when record exists).
     *
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $filePath = database_path('misc/equipment_categories.csv');
        $csv      = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);
        foreach ($records as $record) {
            $chargingInterval = $record['default_charging_interval'];
            if (!in_array($chargingInterval, EquipmentCategoryChargingIntervals::values())) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid interval %s specified, allowed values are: %s',
                    $chargingInterval,
                    implode(',', EquipmentCategoryChargingIntervals::values())
                ));
            }

            $chargingRatePerInterval = $this->parseFloatFromCurrencyString($record['default_charge_rate_per_interval']);
            $chargingRatePerWeek     = $this->parseFloatFromCurrencyString($record['default_charge_rate_per_week']);
            $buyCostPerInterval      = $this->parseFloatFromCurrencyString($record['default_buy_cost_per_interval']);
            $equipmentCategoryData   = [
                'name'                          => $record['name'],
                'is_airmover'                   => $record['is_airmover'] === '1',
                'is_dehum'                      => $record['is_dehum'] === '1',
                'default_buy_cost_per_interval' => $buyCostPerInterval,
            ];

            $equipmentCategoryId = DB::table('equipment_categories')->insertGetId($equipmentCategoryData);

            DB::table('equipment_category_charging_intervals')->insert([
                'equipment_category_id'          => $equipmentCategoryId,
                'charging_interval'              => $chargingInterval,
                'charging_rate_per_interval'     => $chargingRatePerInterval,
                'max_count_to_the_next_interval' => EquipmentCategoryChargingIntervals::DAY === $chargingInterval
                    ? EquipmentCategoryChargingIntervals::DEFAULT_DAY_TO_WEEK_RATE_IN_DAYS
                    : 0,
                'is_default'                     => true,
                'created_at'                     => 'now()',
                'updated_at'                     => 'now()',
            ]);

            if (EquipmentCategoryChargingIntervals::DAY === $chargingInterval && $chargingRatePerWeek > 0) {
                DB::table('equipment_category_charging_intervals')->insert([
                    'equipment_category_id'          => $equipmentCategoryId,
                    'charging_interval'              => EquipmentCategoryChargingIntervals::WEEK,
                    'charging_rate_per_interval'     => $chargingRatePerWeek,
                    'max_count_to_the_next_interval' => 0,
                    'is_default'                     => true,
                    'created_at'                     => 'now()',
                    'updated_at'                     => 'now()',
                ]);
            }
        }
    }
}
