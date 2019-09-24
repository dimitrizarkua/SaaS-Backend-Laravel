<?php

namespace Tests\API\Jobs;

use App\Components\Addresses\Models\Address;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportStatus;
use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\CreditNoteStatus;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Jobs\Enums\ClaimTypes;
use App\Components\Jobs\Enums\JobCriticalityTypes;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Events\JobModelChanged;
use App\Components\Jobs\Events\JobStatusChanged;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\Jobs\Models\JobDocument;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobMessage;
use App\Components\Jobs\Models\JobNote;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobService;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Jobs\Resources\FullJobResource;
use App\Components\Locations\Models\Location;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use App\Http\Responses\Jobs\JobCostingCountersResponse;
use App\Http\Responses\Jobs\JobCostingSummaryResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

/**
 * Class JobsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobsControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.create',
        'jobs.update',
        'jobs.view',
        'jobs.delete',
        'jobs.manage_inbox',
        'jobs.usage.view',
    ];

    public function testCreateJob(): void
    {
        $claimNumber = $this->faker->word;
        $data        = [
            'claim_number' => $claimNumber,
        ];

        $url = action('Jobs\JobsController@store');

        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $jobId = $response->getData('id');
        $job   = Job::find($jobId);
        self::assertNotNull($job);
        self::assertEquals($claimNumber, $job->claim_number);
        self::assertNotNull($job->latestStatus);
        self::assertEquals(JobStatuses::NEW, $job->latestStatus->status);
    }

    /**
     * @see https://pushstack.atlassian.net/browse/SN-644
     */
    public function testJobShouldBeAutoAssignedToUser(): void
    {
        $url      = action('Jobs\JobsController@store');
        $response = $this->postJson($url, ['claim_number' => $this->faker->word]);
        $response->assertStatus(201);

        $job = Job::find($response->getData('id'));
        self::assertTrue($job->assignedUsers->contains($this->user));
    }

    public function testListNotesAndMessages(): void
    {
        /** @var Job $job */
        $job = factory(Job::class)->create();

        $messagesCount = $this->faker->numberBetween(1, 5);
        factory(JobMessage::class, $messagesCount)->create(['job_id' => $job->id]);

        $notesCount = $this->faker->numberBetween(1, 5);
        factory(JobNote::class, $notesCount)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobsController@listNotesAndMessages', ['job_id' => $job->id,]);
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData();

        $messages = $response->getData('messages');
        self::assertCount($messagesCount, $messages);

        $notes = $response->getData('notes');
        self::assertCount($notesCount, $notes);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreateJobWithFullData(): void
    {
        $claimNumber = $this->faker->word;

        $service         = factory(JobService::class)->create();
        $contactCategory = factory(ContactCategory::class)->create([
            'type' => ContactCategoryTypes::INSURER,
        ]);
        /** @var  \App\Components\Contacts\Models\ContactCompanyProfile $companyContact */
        $companyContact = factory(Contact::class)->create([
            'contact_type'        => ContactTypes::COMPANY,
            'contact_category_id' => $contactCategory->id,
        ])->company;
        $actualContract = app()->make(InsurerContractsInterface::class)->createContract(new InsurerContractData(
            [
                'contact_id'       => $companyContact->contact_id,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->subDays(3)->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            ]
        ));
        $address        = factory(Address::class)->create();
        $location       = factory(Location::class)->create();

        $data = [
            'claim_number'             => $claimNumber,
            'job_service_id'           => $service->id,
            'insurer_id'               => $companyContact->contact_id,
            'site_address_id'          => $address->id,
            'site_address_lat'         => $this->faker->latitude,
            'site_address_lng'         => $this->faker->longitude,
            'assigned_location_id'     => $location->id,
            'owner_location_id'        => $location->id,
            'reference_number'         => $this->faker->word,
            'claim_type'               => $this->faker->randomElement(ClaimTypes::values()),
            'criticality'              => $this->faker->randomElement(JobCriticalityTypes::values()),
            'date_of_loss'             => $this->faker->date(),
            'initial_contact_at'       => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'cause_of_loss'            => $this->faker->word,
            'description'              => $this->faker->sentence,
            'anticipated_revenue'      => $this->faker->randomFloat(2),
            'anticipated_invoice_date' => $this->faker->date(),
            'authority_received_at'    => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'expected_excess_payment'  => $this->faker->randomFloat(2),
        ];

        $url = action('Jobs\JobsController@store');

        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $jobId = $response->getData('id');
        $job   = Job::find($jobId);
        self::assertNotNull($job);

        self::assertEquals($claimNumber, $job->claim_number);
        self::assertNotNull($job->latestStatus);
        self::assertEquals(JobStatuses::NEW, $job->latestStatus->status);
        self::assertEquals($service->id, $job->service->id);
        self::assertEquals($companyContact->contact_id, $job->insurer_id);
        self::assertEquals($actualContract->id, $job->insurer_contract_id);
        self::assertEquals($address->id, $job->site_address_id);
        self::assertEquals($data['site_address_lat'], $job->site_address_lat);
        self::assertEquals($data['site_address_lng'], $job->site_address_lng);
        self::assertEquals($location->id, $job->assigned_location_id);
        self::assertEquals($location->id, $job->owner_location_id);
        self::assertEquals($data['reference_number'], $job->reference_number);
        self::assertEquals($data['claim_type'], $job->claim_type);
        self::assertEquals($data['criticality'], $job->criticality);
        self::assertEquals(new Carbon($data['date_of_loss']), $job->date_of_loss);
        self::assertEquals(new Carbon($data['initial_contact_at']), $job->initial_contact_at);
        self::assertEquals($data['cause_of_loss'], $job->cause_of_loss);
        self::assertEquals($data['description'], $job->description);
        self::assertEquals($data['anticipated_revenue'], $job->anticipated_revenue);
        self::assertEquals(new Carbon($data['anticipated_invoice_date']), $job->anticipated_invoice_date);
        self::assertEquals(new Carbon($data['authority_received_at']), $job->authority_received_at);
        self::assertEquals($data['expected_excess_payment'], $job->expected_excess_payment);
    }

    public function testCreateJobWithEmptyParameters(): void
    {
        $url      = action('Jobs\JobsController@store');
        $response = $this->postJson($url);
        $response->assertStatus(201);
    }

    public function testShowEndpoint(): void
    {
        $job = factory(Job::class)->create();
        $url = action('Jobs\JobsController@show', ['id' => $job->id]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(FullJobResource::class);
    }

    public function testNotFoundResponseWhenGettingNotExistingRecord(): void
    {
        $url = action('Jobs\JobsController@show', ['id' => 0]);
        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testFailUpdateClosedJob(): void
    {
        $job  = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $data = [
            'claim_number'             => $this->faker->word,
            'site_address_lat'         => $this->faker->latitude,
            'site_address_lng'         => $this->faker->longitude,
            'reference_number'         => $this->faker->word,
            'claim_type'               => $this->faker->randomElement(ClaimTypes::values()),
            'criticality'              => $this->faker->randomElement(JobCriticalityTypes::values()),
            'date_of_loss'             => $this->faker->date(),
            'initial_contact_at'       => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'cause_of_loss'            => $this->faker->word,
            'anticipated_revenue'      => $this->faker->randomFloat(2),
            'anticipated_invoice_date' => $this->faker->date(),
            'expected_excess_payment'  => $this->faker->randomFloat(2),
        ];

        $url = action('Jobs\JobsController@update', ['id' => $job->id]);
        $this->patchJson($url, $data)
            ->assertStatus(405);
    }

    public function testUpdateNotTouchedAction(): void
    {
        Event::fake([JobModelChanged::class]);

        $job     = $this->fakeJobWithStatus();
        $address = factory(Address::class)->create();

        $data = [
            'claim_number'             => $this->faker->word,
            'site_address_id'          => $address->id,
            'site_address_lat'         => $this->faker->latitude,
            'site_address_lng'         => $this->faker->longitude,
            'reference_number'         => $this->faker->word,
            'claim_type'               => $this->faker->randomElement(ClaimTypes::values()),
            'criticality'              => $this->faker->randomElement(JobCriticalityTypes::values()),
            'date_of_loss'             => $this->faker->date(),
            'initial_contact_at'       => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'cause_of_loss'            => $this->faker->word,
            'anticipated_revenue'      => $this->faker->randomFloat(2),
            'anticipated_invoice_date' => $this->faker->date(),
            'expected_excess_payment'  => $this->faker->randomFloat(2),
        ];

        $url = action('Jobs\JobsController@update', ['id' => $job->id]);
        $this->patchJson($url, $data)
            ->assertStatus(200);

        $reloaded = Job::findOrFail($job->id);
        foreach ($data as $column => $value) {
            $attributeValue = $reloaded->getAttribute($column);
            if ($attributeValue instanceof Carbon) {
                self::assertTrue($attributeValue->eq(new Carbon($value)));
            } else {
                self::assertEquals($attributeValue, $value);
            }
        }
        self::assertTrue($job->touched_at->eq($reloaded->touched_at));

        Event::dispatched(JobModelChanged::class, function ($e) use ($job) {
            self::assertTrue($e->targetId === $job->id);
        });
    }

    public function testUpdateAndTouchedAction(): void
    {
        Event::fake([JobModelChanged::class]);

        $job              = $this->fakeJobWithStatus();
        $jobService       = factory(JobService::class)->create();
        $address          = factory(Address::class)->create();
        $assignedLocation = factory(Location::class)->create();
        $ownerLocation    = factory(Location::class)->create();

        $data = [
            'claim_number'             => $this->faker->word,
            'job_service_id'           => $jobService->id,
            'site_address_id'          => $address->id,
            'site_address_lat'         => $this->faker->latitude,
            'site_address_lng'         => $this->faker->longitude,
            'assigned_location_id'     => $assignedLocation->id,
            'owner_location_id'        => $ownerLocation->id,
            'reference_number'         => $this->faker->word,
            'claim_type'               => $this->faker->randomElement(ClaimTypes::values()),
            'criticality'              => $this->faker->randomElement(JobCriticalityTypes::values()),
            'date_of_loss'             => $this->faker->date(),
            'initial_contact_at'       => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'cause_of_loss'            => $this->faker->word,
            'description'              => $this->faker->word,
            'anticipated_revenue'      => $this->faker->randomFloat(2),
            'anticipated_invoice_date' => $this->faker->date(),
            'authority_received_at'    => $this->faker->date('Y-m-d\TH:i:s\Z'),
            'expected_excess_payment'  => $this->faker->randomFloat(2),
        ];

        $url = action('Jobs\JobsController@update', ['id' => $job->id]);
        $this->patchJson($url, $data)
            ->assertStatus(200);

        $reloaded = Job::findOrFail($job->id);
        foreach ($data as $column => $value) {
            $attributeValue = $reloaded->getAttribute($column);
            if ($attributeValue instanceof Carbon) {
                self::assertTrue($attributeValue->eq(new Carbon($value)));
            } else {
                self::assertEquals($attributeValue, $value);
            }
        }
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));

        Event::dispatched(JobModelChanged::class, function ($e) use ($job) {
            self::assertTrue($e->targetId === $job->id);
        });
    }

    public function testUpdateClaimNumberValidationSuccess(): void
    {
        $job = $this->fakeJobWithStatus();

        $data = [
            'claim_number' => $job->claim_number,
        ];

        $url = action('Jobs\JobsController@update', ['id' => $job->id]);
        $this->patchJson($url, $data)
            ->assertStatus(200);
    }

    public function testUpdateClaimNumberValidationFail(): void
    {
        $job1 = $this->fakeJobWithStatus();
        $job2 = $this->fakeJobWithStatus();

        $data = [
            'claim_number' => $job1->claim_number,
        ];

        $url = action('Jobs\JobsController@update', ['id' => $job2->id]);
        $this->patchJson($url, $data)
            ->assertStatus(422);
    }

    public function testDeleteAction(): void
    {
        /** @var Job $job */
        $job = factory(Job::class)->create();
        self::assertNull($job->deleted_at);
        $url = action('Jobs\JobsController@destroy', ['id' => $job->id]);
        $this->deleteJson($url)
            ->assertStatus(200);

        /** @var Job $job */
        $job = Job::withTrashed()
            ->where('id', $job->id)
            ->first();
        self::assertNotNull($job);
        self::assertNotNull($job->deleted_at);
    }

    public function testMergeJobSuccess(): void
    {
        Event::fake([JobStatusChanged::class]);
        /** @var Job $job */
        $sourceJob      = $this->fakeJobWithStatus();
        $destinationJob = $this->fakeJobWithStatus();

        $count        = $this->faker->numberBetween(1, 5);
        $jobDocuments = factory(JobDocument::class, $count)->create([
            'job_id' => $sourceJob->id,
        ]);

        $count    = $this->faker->numberBetween(1, 5);
        $jobNotes = factory(JobNote::class, $count)->create([
            'job_id' => $sourceJob->id,
        ]);

        $count       = $this->faker->numberBetween(1, 5);
        $jobMessages = factory(JobMessage::class, $count)->create([
            'job_id' => $sourceJob->id,
        ]);

        $url = action('Jobs\JobsController@mergeJob', [
            'source_job_id'      => $sourceJob->id,
            'destination_job_id' => $destinationJob->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $sourceReloaded      = Job::findOrFail($sourceJob->id);
        $destinationReloaded = Job::findOrFail($destinationJob->id);

        self::assertEquals(count($jobMessages), $destinationReloaded->messages()->count());
        self::assertEquals(count($jobDocuments), $destinationReloaded->documents()->count());
        self::assertEquals(count($jobNotes) + 1, $destinationReloaded->notes()->count());
        self::assertEquals(count($jobNotes) + 1, $sourceReloaded->notes()->count());

        $destinationNotes = $destinationReloaded->notes()->pluck('note');
        $noteAboutMerge   = sprintf('Job #%s merged into #%s job.', $sourceReloaded->id, $destinationReloaded->id);
        self::assertTrue($destinationNotes->contains($noteAboutMerge));

        $sourceNotes    = $sourceReloaded->notes()->pluck('note');
        $noteAboutMerge = sprintf('Merged into job #%s from #%s job.', $destinationReloaded->id, $sourceReloaded->id);
        self::assertTrue($sourceNotes->contains($noteAboutMerge));

        self::assertEquals(JobStatuses::CLOSED, $sourceReloaded->getCurrentStatus());
        Event::dispatched(JobStatusChanged::class, function (JobStatusChanged $event) use ($sourceReloaded) {
            self::assertTrue($event->job->id === $sourceReloaded->id);
        });
    }

    public function testFailMergeClosedJob(): void
    {
        /** @var Job $job */
        $sourceJob      = $this->fakeJobWithStatus();
        $destinationJob = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        $url = action('Jobs\JobsController@mergeJob', [
            'source_job_id'      => $sourceJob->id,
            'destination_job_id' => $destinationJob->id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testMergeJobTwiceSuccess(): void
    {
        /** @var Job $job */
        $sourceJob       = factory(Job::class)->create();
        $destinationJob1 = $this->fakeJobWithStatus();
        $destinationJob2 = $this->fakeJobWithStatus();

        $url = action('Jobs\JobsController@mergeJob', [
            'source_job_id'      => $sourceJob->id,
            'destination_job_id' => $destinationJob1->id,
        ]);
        $this->postJson($url)->assertStatus(200);

        $url = action('Jobs\JobsController@mergeJob', [
            'source_job_id'      => $sourceJob->id,
            'destination_job_id' => $destinationJob2->id,
        ]);
        $this->postJson($url)->assertStatus(200);

        $sourceReloaded = Job::findOrFail($sourceJob->id);
        self::assertCount(2, $sourceReloaded->statuses); // 'NEW' and only one 'CLOSED'
    }

    public function testMergeSameJobsSuccess(): void
    {
        /** @var Job $job */
        $sourceJob      = $this->fakeJobWithStatus();
        $destinationJob = $this->fakeJobWithStatus();

        $url = action('Jobs\JobsController@mergeJob', [
            'source_job_id'      => $sourceJob->id,
            'destination_job_id' => $destinationJob->id,
        ]);
        $this->postJson($url)->assertStatus(200);
        $this->postJson($url)->assertStatus(200);
    }

    public function testSnoozeJob(): void
    {
        $job = $this->fakeJobWithStatus();

        $url = action('Jobs\JobsController@snoozeJob', [
            'job_id'        => $job->id,
            'snoozed_until' => $this->faker->date('Y-m-d\TH:i:s\Z'),
        ]);

        $this->postJson($url)
            ->assertStatus(200);
    }

    public function testUnsnoozeJob(): void
    {
        $job = $this->fakeJobWithStatus(null, [
            'snoozed_until' => $this->faker->date(),
        ]);

        $url = action('Jobs\JobsController@unsnoozeJob', [
            'job_id' => $job->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);
    }

    public function testJobShouldBeDuplicated(): void
    {
        /** @var Job $job */
        $job = factory(Job::class)->create();

        $url = action('Jobs\JobsController@duplicate', ['id' => $job->id]);

        $newJobId = $this->postJson($url)
            ->assertStatus(201)
            ->getData('id');

        /** @var Job $newJob */
        $newJob = Job::findOrFail($newJobId);

        self::assertNull($newJob->claim_number);
        self::assertEquals($job->job_service_id, $newJob->job_service_id);
        self::assertEquals($job->insurer_id, $newJob->insurer_id);
        self::assertEquals($job->site_address_id, $newJob->site_address_id);
        self::assertEquals($job->site_address_lat, $newJob->site_address_lat);
        self::assertEquals($job->site_address_lng, $newJob->site_address_lng);
        self::assertEquals($job->assigned_location_id, $newJob->assigned_location_id);
        self::assertEquals($job->owner_location_id, $newJob->owner_location_id);
        self::assertEquals($job->reference_number, $newJob->reference_number);
        self::assertEquals($job->claim_type, $newJob->claim_type);
        self::assertEquals($job->criticality, $newJob->criticality);
        self::assertEquals($job->date_of_loss, $newJob->date_of_loss);
        self::assertEquals($job->initial_contact_at, $newJob->initial_contact_at);
        self::assertEquals($job->cause_of_loss, $newJob->cause_of_loss);
        self::assertEquals($job->description, $newJob->description);
        self::assertEquals($job->anticipated_revenue, $newJob->anticipated_revenue);

        self::assertEmpty($job->assignedUsers);
        self::assertEmpty($job->assignedTeams);
    }

    public function testGetCostingSummary(): void
    {
        $job = factory(Job::class)->create([
            'created_at' => Carbon::now()->subMonth(),
        ]);
        factory(JobLabour::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobMaterial::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobReimbursement::class)->create([
            'job_id'        => $job->id,
            'is_chargeable' => true,
        ]);
        factory(JobLahaCompensation::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        factory(Invoice::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
            ])
            ->each(function (Invoice $invoice) {
                factory(InvoiceItem::class, 3)->create([
                    'invoice_id' => $invoice->id,
                ]);
                factory(InvoiceStatus::class)->create([
                    'invoice_id' => $invoice->id,
                    'status'     => FinancialEntityStatuses::APPROVED,
                ]);
            });
        factory(CreditNote::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
                'date'   => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (CreditNote $creditNote) {
                factory(CreditNoteItem::class, 3)->create([
                    'credit_note_id' => $creditNote->id,
                ]);
                factory(CreditNoteStatus::class)->create([
                    'credit_note_id' => $creditNote->id,
                    'status'         => FinancialEntityStatuses::APPROVED,
                ]);
            });
        factory(PurchaseOrder::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
                'date'   => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (PurchaseOrder $purchaseOrder) {
                factory(PurchaseOrderItem::class, 3)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                ]);
                factory(PurchaseOrderStatus::class)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'status'            => FinancialEntityStatuses::APPROVED,
                ]);
            });
        factory(AssessmentReport::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
            ])
            ->each(function (AssessmentReport $assessmentReport) {
                factory(AssessmentReportCostItem::class, 3)->create([
                    'assessment_report_id' => $assessmentReport->id,
                ]);
                factory(AssessmentReportStatus::class)->create([
                    'assessment_report_id' => $assessmentReport->id,
                    'status'               => AssessmentReportStatuses::CLIENT_APPROVED,
                ]);
            });

        $url = action('Jobs\JobsController@getCostingSummary', ['job_id' => $job->id]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobCostingSummaryResponse::class, true);
    }

    public function testGetCostingCounters(): void
    {
        $job = $this->fakeJobWithStatus();
        factory(JobMaterial::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobLabour::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobAllowance::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobReimbursement::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobLahaCompensation::class)->create([
            'job_id' => $job->id,
        ]);
        factory(PurchaseOrder::class)->create([
            'job_id' => $job->id,
        ]);

        $url = action('Jobs\JobsController@getCostingCounters', ['job_id' => $job->id]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobCostingCountersResponse::class, true);
        $data     = $response->getData();
        self::assertEquals(1, $data['materials']);
        self::assertEquals(1, $data['equipment']);
        //labour, allowance, reimbursement, laha compensation
        self::assertEquals(4, $data['time']);
        self::assertEquals(1, $data['purchase_orders']);
    }
}
