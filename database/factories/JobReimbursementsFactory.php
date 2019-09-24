<?php

use App\Components\Documents\Models\Document;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobReimbursement;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(JobReimbursement::class, function (Faker $faker) {
    return [
        'job_id'          => factory(Job::class)->create()->id,
        'user_id'         => factory(User::class)->create()->id,
        'creator_id'      => factory(User::class)->create()->id,
        'date_of_expense' => Carbon::now()->format('Y-m-d'),
        'document_id'     => factory(Document::class)->create()->id,
        'description'     => $faker->text,
        'total_amount'    => $faker->randomFloat(2, 50, 100),
        'is_chargeable'   => $faker->boolean,
        'invoice_item_id' => factory(InvoiceItem::class)->create()->id,
        'approved_at'     => Carbon::now()->addHour(),
        'approver_id'     => factory(User::class)->create()->id,
    ];
});
