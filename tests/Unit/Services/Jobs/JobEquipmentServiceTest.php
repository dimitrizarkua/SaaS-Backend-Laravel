<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobEquipmentServiceInterface;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobEquipmentChargingInterval;
use App\Components\Jobs\Models\VO\CreateJobEquipmentData;
use App\Components\Locations\Models\Location;
use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use App\Components\UsageAndActuals\Models\EquipmentCategoryInsurerContract;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Unit\Finance\InvoicesTestFactory;
use Tests\Unit\Jobs\JobFaker;
use Tests\Unit\UsageAndActuals\EquipmentTestFactory;

/**
 * Class JobEquipmentServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   equipment
 * @group   services
 */
class JobEquipmentServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobEquipmentServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobEquipmentServiceInterface::class);

        $models       = [
            EquipmentCategoryChargingInterval::class,
            JobEquipmentChargingInterval::class,
            JobEquipment::class,
            EquipmentCategory::class,
            Equipment::class,
            Location::class,
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganization::class,
            Contact::class,
            InvoiceItem::class,
            InvoiceStatus::class,
            Invoice::class,
            User::class,
            ContactCompanyProfile::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testGetJobEquipment(): void
    {
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create();

        $reloaded = $this->service->getJobEquipment($jobEquipment->id);

        self::compareDataWithModel($jobEquipment->toArray(), $reloaded);
    }

    public function testFailToGetJobEquipmentWhenJobEquipmentNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->getJobEquipment(0);
    }

    public function testGetJobListEquipment(): void
    {
        $job   = $this->fakeJobWithStatus();
        $count = $this->faker->numberBetween(2, 4);
        factory(JobEquipment::class, $count)->create([
            'job_id' => $job->id,
        ]);

        $jobEquipment = $this->service->getJobEquipmentList($job->id);

        self::assertCount($count, $jobEquipment);
    }

    public function testFailToGetJobEquipmentListWhenJobNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->getJobEquipmentList(0);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobEquipmentWithoutEndedAtDate(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var User $user */
        $user      = factory(User::class)->create();
        $equipment = EquipmentTestFactory::createEquipmentWithInterval();
        $data      = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $this->faker->date(),
        ]);

        $jobEquipment = $this->service->createJobEquipment($data, $job->id, $user->id);

        self::assertEquals($jobEquipment->job_id, $job->id);
        self::assertEquals($jobEquipment->equipment_id, $equipment->id);
        self::assertEquals($jobEquipment->creator_id, $user->id);
        self::assertEquals($jobEquipment->started_at, new Carbon($data['started_at']));
        self::assertEquals($jobEquipment->ended_at, null);
        self::assertEquals($jobEquipment->interval, $equipment->getDefaultChargingInterval()->charging_interval);
        self::assertEquals($jobEquipment->intervals_count, 0);
        self::assertEquals($jobEquipment->intervals_count_override, 0);
        self::assertEquals($jobEquipment->buy_cost_per_interval, $equipment->category->default_buy_cost_per_interval);
        self::assertNull($jobEquipment->invoice_item_id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobEquipmentWithEndedAtDateAndWeekInterval(): void
    {
        $job        = $this->fakeJobWithStatus();
        $weeksCount = $this->faker->numberBetween(1, 4);
        /** @var User $user */
        $user      = factory(User::class)->create();
        $equipment = EquipmentTestFactory::createEquipmentWithInterval(EquipmentCategoryChargingIntervals::WEEK);
        $startedAt = Carbon::now();
        $endedAt   = (new Carbon($startedAt))->addWeeks($weeksCount);
        $data      = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $startedAt,
            'ended_at'     => $endedAt,
        ]);

        $jobEquipment = $this->service->createJobEquipment($data, $job->id, $user->id);

        self::assertEquals($jobEquipment->started_at, new Carbon($data['started_at']));
        self::assertEquals($jobEquipment->ended_at, new Carbon($data['ended_at']));
        self::assertEquals($jobEquipment->interval, EquipmentCategoryChargingIntervals::WEEK);
        self::assertEquals($jobEquipment->intervals_count, $weeksCount);
        self::assertEquals($jobEquipment->intervals_count_override, $weeksCount);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobEquipmentWithEndedAtDateAndDayInterval(): void
    {
        $job       = $this->fakeJobWithStatus();
        $daysCount = $this->faker->numberBetween(1, 15);
        /** @var User $user */
        $user      = factory(User::class)->create();
        $equipment = EquipmentTestFactory::createEquipmentWithInterval(EquipmentCategoryChargingIntervals::DAY);
        $startedAt = Carbon::now();
        $endedAt   = (new Carbon($startedAt))->addDays($daysCount);
        $data      = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $startedAt,
            'ended_at'     => $endedAt,
        ]);

        $jobEquipment = $this->service->createJobEquipment($data, $job->id, $user->id);

        self::assertEquals($jobEquipment->started_at, new Carbon($data['started_at']));
        self::assertEquals($jobEquipment->ended_at, new Carbon($data['ended_at']));
        self::assertEquals($jobEquipment->interval, EquipmentCategoryChargingIntervals::DAY);
        self::assertEquals($jobEquipment->intervals_count, $daysCount);
        self::assertEquals($jobEquipment->intervals_count_override, $daysCount);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobEquipmentWithEndedAtDateAndHourInterval(): void
    {
        $job        = $this->fakeJobWithStatus();
        $hoursCount = $this->faker->numberBetween(1, 100);
        /** @var User $user */
        $user      = factory(User::class)->create();
        $equipment = EquipmentTestFactory::createEquipmentWithInterval(EquipmentCategoryChargingIntervals::HOUR);
        $startedAt = Carbon::now();
        $endedAt   = (new Carbon($startedAt))->addHours($hoursCount);
        $data      = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $startedAt,
            'ended_at'     => $endedAt,
        ]);

        $jobEquipment = $this->service->createJobEquipment($data, $job->id, $user->id);

        self::assertEquals($jobEquipment->started_at, new Carbon($data['started_at']));
        self::assertEquals($jobEquipment->ended_at, new Carbon($data['ended_at']));
        self::assertEquals($jobEquipment->interval, EquipmentCategoryChargingIntervals::HOUR);
        self::assertEquals($jobEquipment->intervals_count, $hoursCount);
        self::assertEquals($jobEquipment->intervals_count_override, $hoursCount);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobEquipmentWithEndedAtDateAndEachInterval(): void
    {
        $job       = $this->fakeJobWithStatus();
        $daysCount = $this->faker->numberBetween(1, 15);
        /** @var User $user */
        $user      = factory(User::class)->create();
        $equipment = EquipmentTestFactory::createEquipmentWithInterval(EquipmentCategoryChargingIntervals::EACH);
        $startedAt = Carbon::now();
        $endedAt   = (new Carbon($startedAt))->addDays($daysCount);
        $data      = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $startedAt,
            'ended_at'     => $endedAt,
        ]);

        $jobEquipment = $this->service->createJobEquipment($data, $job->id, $user->id);

        self::assertEquals($jobEquipment->started_at, new Carbon($data['started_at']));
        self::assertEquals($jobEquipment->ended_at, new Carbon($data['ended_at']));
        self::assertEquals($jobEquipment->interval, EquipmentCategoryChargingIntervals::EACH);
        self::assertEquals($jobEquipment->intervals_count, 1);
        self::assertEquals($jobEquipment->intervals_count_override, 1);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobEquipmentWhenWhereIsInsurerContractForEquipmentCategory(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Equipment $equipment */
        $equipment = EquipmentTestFactory::createEquipmentWithDefaultAndInsurerContractIntervals(
            $job->insurer_contract_id
        );
        $category  = $equipment->category;
        /** @var EquipmentCategoryChargingInterval $contractInterval */
        $contractInterval = $category->chargingIntervals()
            ->where('is_default', false)
            ->first();
        /** @var EquipmentCategoryInsurerContract $insurerContract */
        $insurerContract = EquipmentCategoryInsurerContract::query()
            ->where('equipment_category_charging_interval_id', $contractInterval->id)
            ->where('insurer_contract_id', $job->insurer_contract_id)
            ->first();
        $data            = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $this->faker->date(),
        ]);

        $jobEquipment = $this->service->createJobEquipment($data, $job->id, $user->id);

        self::assertEquals($jobEquipment->job_id, $job->id);
        self::assertEquals($jobEquipment->equipment_id, $equipment->id);
        self::assertEquals($jobEquipment->creator_id, $user->id);
        self::assertEquals($jobEquipment->started_at, new Carbon($data['started_at']));
        self::assertEquals($jobEquipment->ended_at, null);
        self::assertEquals($jobEquipment->interval, $contractInterval->charging_interval);
        self::assertEquals($jobEquipment->intervals_count, 0);
        self::assertEquals($jobEquipment->intervals_count_override, 0);
        self::assertEquals($jobEquipment->buy_cost_per_interval, $category->default_buy_cost_per_interval);

        /** @var JobEquipmentChargingInterval $jobEquipmentInterval */
        $jobEquipmentInterval = $jobEquipment->chargingIntervals()
            ->first();

        self::assertNotNull($jobEquipmentInterval);
        self::assertEquals($jobEquipmentInterval->equipment_category_charging_interval_id, $contractInterval->id);
        self::assertEquals($jobEquipmentInterval->charging_interval, $contractInterval->charging_interval);
        self::assertEquals(
            $jobEquipmentInterval->charging_rate_per_interval,
            $contractInterval->charging_rate_per_interval
        );
        self::assertEquals(
            $jobEquipmentInterval->max_count_to_the_next_interval,
            $contractInterval->max_count_to_the_next_interval
        );
        self::assertEquals($jobEquipmentInterval->up_to_amount, $insurerContract->up_to_amount);
        self::assertEquals($jobEquipmentInterval->up_to_interval_count, $insurerContract->up_to_interval_count);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobEquipmentWithDefaultDayAndWeekIntervals(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Equipment $equipment */
        $equipment = EquipmentTestFactory::createEquipmentWithDayAndWeekIntervals();
        /** @var EquipmentCategoryChargingInterval $categoryDayInterval */
        $categoryDayInterval = $equipment->category->chargingIntervals()
            ->where('charging_interval', EquipmentCategoryChargingIntervals::DAY)
            ->first();
        /** @var EquipmentCategoryChargingInterval $categoryWeekInterval */
        $categoryWeekInterval = $equipment->category->chargingIntervals()
            ->where('charging_interval', EquipmentCategoryChargingIntervals::WEEK)
            ->first();
        $data                 = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $this->faker->date(),
        ]);

        $jobEquipment = $this->service->createJobEquipment($data, $job->id, $user->id);

        self::assertCount($equipment->category->chargingIntervals()->count(), $jobEquipment->chargingIntervals);
        self::assertEquals($jobEquipment->job_id, $job->id);
        self::assertEquals($jobEquipment->equipment_id, $equipment->id);
        self::assertEquals($jobEquipment->creator_id, $user->id);
        self::assertEquals($jobEquipment->started_at, new Carbon($data['started_at']));
        self::assertEquals($jobEquipment->ended_at, null);
        self::assertEquals($jobEquipment->interval, $categoryDayInterval->charging_interval);
        self::assertEquals($jobEquipment->intervals_count, 0);
        self::assertEquals($jobEquipment->intervals_count_override, 0);
        self::assertEquals($jobEquipment->buy_cost_per_interval, $equipment->category->default_buy_cost_per_interval);

        /** @var JobEquipmentChargingInterval $jobEquipmentDayInterval */
        $jobEquipmentDayInterval = $jobEquipment->chargingIntervals()
            ->where('charging_interval', EquipmentCategoryChargingIntervals::DAY)
            ->first();
        /** @var JobEquipmentChargingInterval $jobEquipmentWeekInterval */
        $jobEquipmentWeekInterval = $jobEquipment->chargingIntervals()
            ->where('charging_interval', EquipmentCategoryChargingIntervals::WEEK)
            ->first();

        self::assertEquals(
            $jobEquipmentDayInterval->equipment_category_charging_interval_id,
            $categoryDayInterval->id
        );
        self::assertEquals(
            $jobEquipmentDayInterval->charging_interval,
            $categoryDayInterval->charging_interval
        );
        self::assertEquals(
            $jobEquipmentDayInterval->charging_rate_per_interval,
            $categoryDayInterval->charging_rate_per_interval
        );
        self::assertEquals(
            $jobEquipmentDayInterval->max_count_to_the_next_interval,
            $categoryDayInterval->max_count_to_the_next_interval
        );
        self::assertEquals(
            $jobEquipmentWeekInterval->equipment_category_charging_interval_id,
            $categoryWeekInterval->id
        );
        self::assertEquals(
            $jobEquipmentWeekInterval->charging_interval,
            $categoryWeekInterval->charging_interval
        );
        self::assertEquals(
            $jobEquipmentWeekInterval->charging_rate_per_interval,
            $categoryWeekInterval->charging_rate_per_interval
        );
        self::assertEquals(
            $jobEquipmentWeekInterval->max_count_to_the_next_interval,
            $categoryWeekInterval->max_count_to_the_next_interval
        );
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateJobEquipmentWhenEquipmentIsUsedOnSiteNow(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'   => $job->id,
            'ended_at' => null,
        ]);
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Equipment $equipment */
        $data = new CreateJobEquipmentData([
            'equipment_id' => $jobEquipment->equipment_id,
            'started_at'   => $this->faker->date(),
        ]);

        $this->expectExceptionMessage('Could not add equipment because it is used on site now.');
        $this->expectException(NotAllowedException::class);
        $this->service->createJobEquipment($data, $job->id, $user->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateJobEquipmentWhenJobIsClosed(): void
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        $data      = new CreateJobEquipmentData([
            'equipment_id' => $equipment->id,
            'started_at'   => $this->faker->date(),
        ]);

        $this->expectExceptionMessage('Could not add equipment to the closed or cancelled job.');
        $this->expectException(NotAllowedException::class);
        $this->service->createJobEquipment($data, $job->id, $user->id);
    }

    public function testFinishJobEquipmentUsing(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'   => $job->id,
            'ended_at' => null,
        ]);
        $endedAt      = $jobEquipment->started_at->copy()->addDay();

        $updatedJobEquipment = $this->service->finishJobEquipmentUsing($jobEquipment->id, $endedAt);

        self::assertEquals($updatedJobEquipment->ended_at, $endedAt);
        self::assertGreaterThan(0, $updatedJobEquipment->intervals_count);
        self::assertGreaterThan(0, $updatedJobEquipment->intervals_count_override);
    }

    public function testFailToFinishJobEquipmentUsingWhenJobIsClosed(): void
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        $endedAt      = $jobEquipment->started_at->copy()->addDay();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Could not edit job equipment used on the closed or cancelled job.');
        $this->service->finishJobEquipmentUsing($jobEquipment->id, $endedAt);
    }

    public function testFailToFinishJobEquipmentUsingWhenEndedAtIsLessThanStartedAt(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'   => $job->id,
            'ended_at' => null,
        ]);
        $endedAt      = $jobEquipment->started_at->copy()->subDay();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Ended at must be greater or equal than started at.');
        $this->service->finishJobEquipmentUsing($jobEquipment->id, $endedAt);
    }

    public function testFailToFinishJobEquipmentUsingWhenEndedAtIsAlreadySet(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        $endedAt      = new Carbon();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Could not edit job equipment ended at date because it is already set.');
        $this->service->finishJobEquipmentUsing($jobEquipment->id, $endedAt);
    }

    public function testFailToFinishJobEquipmentUsingWhenJobEquipmentUsedOnApprovedInvoice(): void
    {
        /** @var Invoice $invoice */
        $invoice = InvoicesTestFactory::createInvoices(1, [], FinancialEntityStatuses::APPROVED)->first();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
        ]);
        $job         = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => $invoiceItem->id,
        ]);
        $endedAt      = new Carbon();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Could not edit job equipment used on the approved invoice.');
        $this->service->finishJobEquipmentUsing($jobEquipment->id, $endedAt);
    }

    public function testOverrideJobEquipmentIntervalsCount(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        $count        = $this->faker->numberBetween(1, 9);

        $updatedJobEquipment = $this->service->overrideJobEquipmentIntervalsCount($jobEquipment->id, $count);

        self::assertEquals($updatedJobEquipment->intervals_count_override, $count);
    }

    public function testFailToOverrideJobEquipmentIntervalsCountWhenEndedAtIsNotSet(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'   => $job->id,
            'ended_at' => null,
        ]);
        $count        = $this->faker->numberBetween(1, 9);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Could not edit job equipment intervals count override because ended at date is not set.'
        );
        $this->service->overrideJobEquipmentIntervalsCount($jobEquipment->id, $count);
    }

    public function testDeleteJobEquipment(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);

        $this->service->deleteJobEquipment($jobEquipment->id);

        $this->expectException(ModelNotFoundException::class);
        JobEquipment::whereId($jobEquipment->id)
            ->firstOrFail();
    }

    public function testFailToDeleteJobEquipmentWhenJobIsClosed(): void
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Could not edit job equipment used on the closed or cancelled job.');
        $this->service->deleteJobEquipment($jobEquipment->id);
    }

    public function testFailToDeleteJobEquipmentUsedOnApprovedInvoice(): void
    {
        /** @var Invoice $invoice */
        $invoice = InvoicesTestFactory::createInvoices(1, [], FinancialEntityStatuses::APPROVED)->first();
        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
        ]);
        $job         = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => $invoiceItem->id,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Could not edit job equipment used on the approved invoice.');
        $this->service->deleteJobEquipment($jobEquipment->id);
    }

    public function testGetTotalAmountWhenThereAreNoInsurerContractAndNoDayToWeekInterval(): void
    {
        $job            = $this->fakeJobWithStatus();
        $intervalsCount = $this->faker->numberBetween(1, 9);
        $chargingRate   = $this->faker->randomFloat(2, 1, 100);
        $data           = [
            'intervals_count'            => $intervalsCount,
            'charging_rate_per_interval' => $chargingRate,
        ];
        EquipmentTestFactory::createJobEquipmentWithInterval($job->id, $data);

        $totalAmount = $this->service->getJobEquipmentTotalAmount($job->id);

        self::assertArrayHasKey('total_amount', $totalAmount);
        self::assertArrayHasKey('total_amount_for_insurer', $totalAmount);
        self::assertEquals($totalAmount['total_amount'], $intervalsCount * $chargingRate);
        self::assertEquals($totalAmount['total_amount_for_insurer'], $intervalsCount * $chargingRate);
    }

    public function testGetTotalAmountWhenThereIsInsurerContractUpToAmount(): void
    {
        $job            = $this->fakeJobWithStatus();
        $intervalsCount = $this->faker->numberBetween(1, 9);
        $chargingRate   = $this->faker->randomFloat(2, 500, 1000);
        $upToAmount     = $chargingRate - $this->faker->randomFloat(2, 1, 100);
        $data           = [
            'intervals_count'            => $intervalsCount,
            'charging_rate_per_interval' => $chargingRate,
            'up_to_amount'               => $upToAmount,
        ];
        EquipmentTestFactory::createJobEquipmentWithInterval($job->id, $data);

        $totalAmount = $this->service->getJobEquipmentTotalAmount($job->id);

        self::assertArrayHasKey('total_amount', $totalAmount);
        self::assertArrayHasKey('total_amount_for_insurer', $totalAmount);
        self::assertEquals($totalAmount['total_amount'], $intervalsCount * $chargingRate);
        self::assertEquals($totalAmount['total_amount_for_insurer'], $upToAmount);
    }

    public function testGetTotalAmountWhenThereIsInsurerContractUpToIntervalCount(): void
    {
        $job                = $this->fakeJobWithStatus();
        $intervalsCount     = $this->faker->numberBetween(10, 19);
        $chargingRate       = $this->faker->randomFloat(2, 500, 1000);
        $upToIntervalsCount = $intervalsCount - $this->faker->numberBetween(1, 5);
        $data               = [
            'intervals_count'            => $intervalsCount,
            'charging_rate_per_interval' => $chargingRate,
            'up_to_interval_count'       => $upToIntervalsCount,
        ];
        EquipmentTestFactory::createJobEquipmentWithInterval($job->id, $data);

        $totalAmount = $this->service->getJobEquipmentTotalAmount($job->id);

        self::assertArrayHasKey('total_amount', $totalAmount);
        self::assertArrayHasKey('total_amount_for_insurer', $totalAmount);
        self::assertEquals($totalAmount['total_amount'], $intervalsCount * $chargingRate);
        self::assertEquals($totalAmount['total_amount_for_insurer'], $upToIntervalsCount * $chargingRate);
    }

    public function testGetTotalAmountWhenThereIsDayToWeekInterval(): void
    {
        $job                        = $this->fakeJobWithStatus();
        $intervalsCount             = $this->faker->numberBetween(4, 14);
        $chargingRate               = $this->faker->randomFloat(2, 500, 1000);
        $maxCountToTheNextInterval  = $intervalsCount - 1;
        $totalAmountForWeekInterval = $chargingRate * ceil($intervalsCount / 7);
        $data                       = [
            'intervals_count'                => $intervalsCount,
            'charging_rate_per_interval'     => $chargingRate,
            'max_count_to_the_next_interval' => $maxCountToTheNextInterval,
        ];
        EquipmentTestFactory::createJobEquipmentWithInterval($job->id, $data, true);

        $totalAmount = $this->service->getJobEquipmentTotalAmount($job->id);

        self::assertArrayHasKey('total_amount', $totalAmount);
        self::assertArrayHasKey('total_amount_for_insurer', $totalAmount);
        self::assertEquals($totalAmount['total_amount'], $totalAmountForWeekInterval);
        self::assertEquals($totalAmount['total_amount_for_insurer'], $totalAmountForWeekInterval);
    }
}
