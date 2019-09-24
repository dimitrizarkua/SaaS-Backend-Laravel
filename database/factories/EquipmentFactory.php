<?php

use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Equipment::class, function (Faker $faker) {
    return [
        'barcode'               => $faker->sentence(2),
        'equipment_category_id' => function () {
            return factory(EquipmentCategory::class)->create()->id;
        },
        'location_id'           => null,
        'make'                  => $faker->word,
        'model'                 => $faker->word,
        'serial_number'         => $faker->word,
        'last_test_tag_at'      => $faker->date('Y-m-d\TH:i:s\Z'),
    ];
});
