<?php

namespace Tests\Unit\UsageAndActuals\Services;

use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Interfaces\EquipmentCategoriesInterface;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use App\Components\UsageAndActuals\Models\VO\EquipmentCategoryData;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class EquipmentCategoriesServiceTest
 *
 * @package Tests\Unit\UsageAndActuals\Services
 * @group   equipment
 * @group   services
 */
class EquipmentCategoriesServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \App\Components\UsageAndActuals\Interfaces\EquipmentCategoriesInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(EquipmentCategoriesInterface::class);

        $models       = [
            EquipmentCategoryChargingInterval::class,
            EquipmentCategory::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateEquipmentCategoryAndChargingInterval()
    {
        $chargingInterval      = $this->faker->randomElement(EquipmentCategoryChargingIntervals::values());
        $data                  = [
            'name'                           => $this->faker->unique()->sentence(2),
            'is_airmover'                    => $this->faker->boolean,
            'is_dehum'                       => $this->faker->boolean,
            'default_buy_cost_per_interval'  => $this->faker->randomFloat(2, 1, 1000),
            'charging_interval'              => $chargingInterval,
            'charging_rate_per_interval'     => $this->faker->randomFloat(2, 1, 1000),
            'max_count_to_the_next_interval' => 0,
        ];
        $equipmentCategoryData = new EquipmentCategoryData($data);

        $equipmentCategory = $this->service->createEquipmentCategory($equipmentCategoryData);
        /** @var EquipmentCategoryChargingInterval $chargingInterval */
        $chargingInterval = $equipmentCategory->chargingIntervals->first();

        self::assertEquals($equipmentCategory->name, $data['name']);
        self::assertEquals($equipmentCategory->is_airmover, $data['is_airmover']);
        self::assertEquals($equipmentCategory->is_dehum, $data['is_dehum']);
        self::assertEquals($equipmentCategory->default_buy_cost_per_interval, $data['default_buy_cost_per_interval']);
        self::assertNotNull($chargingInterval);
        self::assertEquals($chargingInterval->charging_rate_per_interval, $data['charging_rate_per_interval']);
        self::assertEquals($chargingInterval->charging_interval, $data['charging_interval']);
        self::assertEquals($chargingInterval->max_count_to_the_next_interval, 0);
        self::assertTrue($chargingInterval->is_default);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateEquipmentCategoryAndDayChargingIntervalWithWeekChargingInterval()
    {
        $data                  = [
            'name'                           => $this->faker->unique()->sentence(2),
            'is_airmover'                    => $this->faker->boolean,
            'is_dehum'                       => $this->faker->boolean,
            'default_buy_cost_per_interval'  => $this->faker->randomFloat(2, 1, 1000),
            'charging_interval'              => EquipmentCategoryChargingIntervals::DAY,
            'charging_rate_per_interval'     => $this->faker->randomFloat(2, 1, 1000),
            'max_count_to_the_next_interval' => $this->faker->numberBetween(1, 9),
            'charge_rate_per_week'           => $this->faker->randomFloat(2, 1, 1000),
        ];
        $equipmentCategoryData = new EquipmentCategoryData($data);

        $equipmentCategory = $this->service->createEquipmentCategory($equipmentCategoryData);
        $chargingIntervals = $equipmentCategory->chargingIntervals;
        /** @var EquipmentCategoryChargingInterval $dayInterval */
        $dayInterval = $chargingIntervals->where('charging_interval', EquipmentCategoryChargingIntervals::DAY)
            ->first();
        /** @var EquipmentCategoryChargingInterval $weekInterval */
        $weekInterval = $chargingIntervals->where('charging_interval', EquipmentCategoryChargingIntervals::WEEK)
            ->first();

        self::assertEquals($equipmentCategory->name, $data['name']);
        self::assertEquals($equipmentCategory->is_airmover, $data['is_airmover']);
        self::assertEquals($equipmentCategory->is_dehum, $data['is_dehum']);
        self::assertEquals($equipmentCategory->default_buy_cost_per_interval, $data['default_buy_cost_per_interval']);
        self::assertCount(2, $chargingIntervals);
        self::assertNotNull($dayInterval);
        self::assertEquals($dayInterval->charging_rate_per_interval, $data['charging_rate_per_interval']);
        self::assertEquals($dayInterval->max_count_to_the_next_interval, $data['max_count_to_the_next_interval']);
        self::assertTrue($dayInterval->is_default);
        self::assertNotNull($weekInterval);
        self::assertEquals($weekInterval->charging_rate_per_interval, $data['charge_rate_per_week']);
        self::assertEquals($weekInterval->max_count_to_the_next_interval, 0);
        self::assertTrue($weekInterval->is_default);
    }
}
