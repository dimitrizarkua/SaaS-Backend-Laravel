<?php

use App\Components\Finance\Models\InvoiceItem;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\UsageAndActuals\Models\Material;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(JobMaterial::class, function (Faker $faker) {
    return [
        'job_id'                 => factory(Job::class)->create()->id,
        'material_id'            => factory(Material::class)->create()->id,
        'creator_id'             => factory(User::class)->create()->id,
        'used_at'                => Carbon::now(),
        'sell_cost_per_unit'     => $faker->randomFloat(2, 100, 200),
        'buy_cost_per_unit'      => $faker->randomFloat(2, 50, 100),
        'quantity_used'          => $faker->numberBetween(1, 5),
        'quantity_used_override' => $faker->numberBetween(1, 10),
        'invoice_item_id'        => factory(InvoiceItem::class)->create()->id,
    ];
});
