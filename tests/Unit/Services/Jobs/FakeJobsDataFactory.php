<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Jobs\Enums\ClaimTypes;
use App\Components\Jobs\Enums\JobCriticalityTypes;
use App\Components\Jobs\Enums\RecurrenceRules;
use App\Components\Jobs\Models\JobService;
use App\Components\Jobs\Models\VO\JobCreationData;
use App\Components\Jobs\Models\VO\RecurringJobCreationData;
use App\Components\Locations\Models\Location;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Recurr\Rule;

/**
 * Class FakeJobsDataFactory
 *
 * @package Tests\Unit\Service\Jobs
 */
class FakeJobsDataFactory
{
    /**
     * Returns full job instance where all fields was filled.
     *
     * @return \App\Components\Jobs\Models\VO\JobCreationData
     *
     * @throws \JsonMapper_Exception
     */
    public static function getFullJobDataInstance()
    {
        $faker = Faker::create();

        $jobService    = factory(JobService::class)->create();
        $insurer       = factory(Contact::class)->create();
        $siteAddress   = factory(Address::class)->create();
        $location      = factory(Location::class)->create();
        $ownerLocation = factory(Location::class)->create();

        $jobData                           = new JobCreationData();
        $jobData->job_service_id           = $jobService->id;
        $jobData->insurer_id               = $insurer->id;
        $jobData->site_address_id          = $siteAddress->id;
        $jobData->site_address_lat         = $faker->latitude;
        $jobData->site_address_lng         = $faker->longitude;
        $jobData->assigned_location_id     = $location->id;
        $jobData->owner_location_id        = $ownerLocation->id;
        $jobData->reference_number         = $faker->word;
        $jobData->claim_type               = $faker->randomElement(ClaimTypes::values());
        $jobData->criticality              = $faker->randomElement(JobCriticalityTypes::values());
        $jobData->date_of_loss             = Carbon::today();
        $jobData->cause_of_loss            = $faker->sentence;
        $jobData->description              = $faker->sentence;
        $jobData->anticipated_revenue      = $faker->randomFloat(2);
        $jobData->anticipated_invoice_date = Carbon::today();
        $jobData->authority_received_at    = Carbon::today();
        $jobData->expected_excess_payment  = $faker->randomFloat(2);

        return $jobData;
    }

    /**
     * @param string|null $rule
     *
     * @return \App\Components\Jobs\Models\VO\RecurringJobCreationData
     *
     * @throws \JsonMapper_Exception
     * @throws \Recurr\Exception\InvalidRRule
     */
    public static function getRecurringJobDataInstance(string $rule = null)
    {
        $faker = Faker::create();

        $contactCategory = factory(ContactCategory::class)->create([
            'type' => ContactCategoryTypes::INSURER,
        ]);
        $insurer = factory(Contact::class)->create([
            'contact_type'        => ContactTypes::COMPANY,
            'contact_category_id' => $contactCategory->id,
        ]);
        $jobService  = factory(JobService::class)->create();
        $siteAddress = factory(Address::class)->create();
        $location    = factory(Location::class)->create();

        $jobData = new RecurringJobCreationData();

        $cnt = count(RecurrenceRules::$values);

        if (null === $rule) {
            $rule = RecurrenceRules::$values[$faker->numberBetween(0, $cnt - 1)];
        }

        $recRule = new Rule($rule);

        $startDate = null === $recRule->getStartDate()
            ? Carbon::today()
            : $recRule->getStartDate();

        $startDate->setTime(0, 0, 0);
        $recRule->setStartDate($startDate, true);

        $jobData->setRecurrenceRule($recRule->getString())
            ->setJobServiceId($jobService->id)
            ->setInsurerId($insurer->id)
            ->setSiteAddressId($siteAddress->id)
            ->setOwnerLocationId($location->id)
            ->setDescription($faker->sentence);

        return $jobData;
    }
}
