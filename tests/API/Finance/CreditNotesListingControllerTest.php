<?php

namespace Tests\API\Finance;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteApproveRequest;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\CreditNoteStatus;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Services\CreditNoteService;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Helpers\Decimal;
use App\Http\Responses\Finance\CreditNoteInfoResponse;
use App\Http\Responses\Finance\CreditNoteListResponse;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class CreditNotesListingControllerTest
 *
 * @package Tests\API\Finance
 *
 * @group   finance
 * @group   credit-note
 * @group   finance-listings
 */
class CreditNotesListingControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.credit_notes.view',
    ];

    /**
     * @var CreditNoteService
     */
    private $service;
    /**
     * @var AccountingOrganization
     */
    private $accountOrganization;
    /**
     * @var GLAccount
     */
    private $salesAccount;

    public function setUp(): void
    {
        parent::setUp();
        $models       = [
            GSCode::class,
            GLAccount::class,
            CreditNote::class,
            AccountingOrganization::class,
            Location::class,
        ];
        $this->models = array_merge($models, $this->models);

        $this->service = $this->app->get(CreditNoteService::class);
        /** @var AccountingOrganization $accountOrganiztion */
        $this->accountOrganization = factory(AccountingOrganization::class)->create();

        $revenueAccountType = factory(AccountType::class)->create([
            'name'                     => 'Revenue',
            'increase_action_is_debit' => false,
        ]);

        $this->salesAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'account_type_id'            => $revenueAccountType->id,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function testGetInfoMethod(): void
    {
        $location = factory(Location::class)->create();
        /** @var \Illuminate\Database\Eloquent\Collection $creditNotes */
        $creditNotes = factory(CreditNote::class, 3)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        foreach ($creditNotes as $creditNote) {
            factory(CreditNoteItem::class, 3)->create([
                'credit_note_id' => $creditNote->id,
                'gl_account_id'  => $this->salesAccount->id,
            ]);
        }
        $limit = max(
            $creditNotes[1]->getTotalAmount(),
            $creditNotes[2]->getTotalAmount()
        );
        $this->user->update([
            'credit_note_approval_limit' => $limit + 1,
        ]);
        $this->user->locations()->attach($location);
        $this->service->createApproveRequest(
            $creditNotes[1]->id,
            factory(User::class)->create()->id,
            [$this->user->id]
        );
        $this->service->approve($creditNotes[2]->id, $this->user);
        $url = action('Finance\CreditNoteListingController@getInfo');
        $this->actingAs($this->user->fresh());

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(CreditNoteInfoResponse::class, true)
            ->getData();

        $draftAmount           = $response['draft']['amount'];
        $pendingApprovalAmount = $response['pending_approval']['amount'];
        $approvedAmount        = $response['approved']['amount'];
        self::assertEquals($response['draft']['count'], 1);
        self::assertTrue(Decimal::areEquals($draftAmount, $creditNotes[0]->getTotalAmount()));
        self::assertEquals($response['pending_approval']['count'], 1);
        self::assertTrue(Decimal::areEquals($pendingApprovalAmount, $creditNotes[1]->getTotalAmount()));
        self::assertEquals($response['approved']['count'], 1);
        self::assertTrue(Decimal::areEquals($approvedAmount, $creditNotes[2]->getTotalAmount()));
    }

    /**
     * @throws \Throwable
     */
    public function testGetInfoMethodWithFiltration(): void
    {
        /** @var Location $firstLocation */
        $firstLocation = factory(Location::class)->create();

        $countOfDraftForFirstLocation    = $this->faker->numberBetween(1, 2);
        $countOfPendingForFirstLocation  = $this->faker->numberBetween(1, 2);
        $countOfApprovedForFirstLocation = $this->faker->numberBetween(1, 2);

        $this->createCreditNotes(
            $firstLocation,
            $countOfDraftForFirstLocation,
            $countOfPendingForFirstLocation,
            $countOfApprovedForFirstLocation
        );

        /** @var Location $firstLocation */
        $secondLocation = factory(Location::class)->create();

        $countOfDraftForSecondLocation    = $this->faker->numberBetween(1, 2);
        $countOfPendingForSecondLocation  = $this->faker->numberBetween(1, 2);
        $countOfApprovedForSecondLocation = $this->faker->numberBetween(1, 2);

        $this->createCreditNotes(
            $secondLocation,
            $countOfDraftForSecondLocation,
            $countOfPendingForSecondLocation,
            $countOfApprovedForSecondLocation
        );

        $url = action('Finance\CreditNoteListingController@getInfo', [
            'locations' => [$firstLocation->id],
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(CreditNoteInfoResponse::class, true)
            ->getData();

        self::assertEquals($countOfDraftForFirstLocation, $response['draft']['count']);
        self::assertEquals($countOfPendingForFirstLocation, $response['pending_approval']['count']);
        self::assertEquals($countOfApprovedForFirstLocation, $response['approved']['count']);
    }

    /**
     * @param Location $location
     * @param int      $countOfdDraft
     * @param int      $countOfPending
     * @param int      $countOfApproved
     */
    private function createCreditNotes(
        Location $location,
        int $countOfdDraft,
        int $countOfPending,
        int $countOfApproved
    ): void {

        // Draft
        factory(CreditNote::class, $countOfdDraft)
            ->create([
                'location_id'                => $location->id,
                'accounting_organization_id' => $this->accountOrganization->id,
            ])
            ->each(function (CreditNote $creditNote) {
                factory(CreditNoteItem::class, 3)->create([
                    'credit_note_id' => $creditNote->id,
                    'gl_account_id'  => $this->salesAccount->id,
                ]);
            });

        //Pending approval
        factory(CreditNote::class, $countOfPending)
            ->create([
                'location_id'                => $location->id,
                'accounting_organization_id' => $this->accountOrganization->id,
            ])
            ->each(function (CreditNote $creditNote) {
                factory(CreditNoteItem::class, 3)->create([
                    'credit_note_id' => $creditNote->id,
                    'gl_account_id'  => $this->salesAccount->id,
                ]);
                factory(CreditNoteApproveRequest::class)->create([
                    'credit_note_id' => $creditNote->id,
                    'approved_at'    => null,
                ]);
            });

        //Approved
        factory(CreditNote::class, $countOfApproved)
            ->create([
                'location_id'                => $location->id,
                'accounting_organization_id' => $this->accountOrganization->id,
            ])
            ->each(function (CreditNote $creditNote) {
                factory(CreditNoteItem::class, 3)->create([
                    'credit_note_id' => $creditNote->id,
                    'gl_account_id'  => $this->salesAccount->id,
                ]);
                factory(CreditNoteStatus::class)->create([
                    'credit_note_id' => $creditNote->id,
                    'status'         => FinancialEntityStatuses::APPROVED,
                ]);
            });
    }

    public function testGetDraftMethod(): void
    {
        $location = factory(Location::class)->create();
        $contact  = factory(Contact::class)->create();
        $job      = factory(Job::class)->create();
        $date     = Carbon::now()->addDays($this->faker->numberBetween(10, 20))->toDateString();
        $count    = $this->faker->numberBetween(1, 3);
        /** @var \Illuminate\Database\Eloquent\Collection $creditNotes */
        $creditNotes = factory(CreditNote::class, $count)->create(
            [
                'location_id'          => $location->id,
                'recipient_contact_id' => $contact->id,
                'job_id'               => $job->id,
                'date'                 => $date,
            ]
        );
        foreach ($creditNotes as $creditNote) {
            factory(CreditNoteItem::class, 3)->create(['credit_note_id' => $creditNote->id]);
        }
        $this->user->locations()->attach($location);

        $url = action('Finance\CreditNoteListingController@getDraft', [
            'location_id'          => $location->id,
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date'                 => $date,
        ]);
        $this->actingAs($this->user->fresh());
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count)
            ->assertValidSchema(CreditNoteListResponse::class, true);
    }

    public function testGetPendingApprovalMethod(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);
        $contact = factory(Contact::class)->create();
        $job     = factory(Job::class)->create();
        $date    = Carbon::now()->addDays($this->faker->numberBetween(10, 20))->toDateString();
        $count   = $this->faker->numberBetween(1, 3);

        factory(CreditNote::class, $count)
            ->create([
                'location_id'          => $location->id,
                'recipient_contact_id' => $contact->id,
                'job_id'               => $job->id,
                'date'                 => $date,
            ])
            ->each(function (CreditNote $creditNote) {
                $this->user->saveOrFail();
                factory(CreditNoteItem::class, 3)->create(['credit_note_id' => $creditNote->id]);

                $totalAmount = $creditNote->getTotalAmount();
                if (null === $this->user->credit_note_approval_limit ||
                    $this->user->credit_note_approval_limit < $totalAmount) {
                    $this->user->credit_note_approval_limit = $totalAmount + 1;
                    $this->user->saveOrFail();
                }

                $this->service->createApproveRequest(
                    $creditNote->id,
                    factory(User::class)->create()->id,
                    [$this->user->id]
                );
            });

        $url = action('Finance\CreditNoteListingController@getPendingApproval');
        $this->actingAs($this->user->fresh());
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count)
            ->assertValidSchema(CreditNoteListResponse::class, true);
    }

    public function testGetApprovedMethod(): void
    {
        $location = factory(Location::class)->create();
        $contact  = factory(Contact::class)->create();
        $job      = factory(Job::class)->create();
        $date     = Carbon::now()->addDays($this->faker->numberBetween(10, 20))->toDateString();
        $count    = $this->faker->numberBetween(1, 3);

        /** @var CreditNote[] $creditNotes */
        $creditNotes = factory(CreditNote::class, $count)->create([
            'location_id'          => $location->id,
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date'                 => $date,
        ]);

        foreach ($creditNotes as $creditNote) {
            factory(CreditNoteItem::class, 3)->create(['credit_note_id' => $creditNote->id]);
            factory(CreditNoteStatus::class)->create([
                'credit_note_id' => $creditNote->id,
                'status'         => FinancialEntityStatuses::APPROVED,
                'user_id'        => $this->user->id,
            ]);
        }
        $this->user->locations()->attach($location);

        $url = action('Finance\CreditNoteListingController@getApproved', [
            'location_id'          => $location->id,
            'recipient_contact_id' => $contact->id,
            'job_id'               => $job->id,
            'date'                 => $date,
        ]);
        $this->actingAs($this->user->fresh());
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count)
            ->assertValidSchema(CreditNoteListResponse::class, true);
    }

    public function testAllMethod(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $countOfCreditNotes = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditNotes)
            ->create([
                'location_id' => $location->id,
            ]);

        $url = action('Finance\CreditNoteListingController@getAll');

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfCreditNotes)
            ->assertValidSchema(CreditNoteListResponse::class, true);
    }

    public function testFiltrationByDateTo(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $countOfCreditBeforeDate = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditBeforeDate)
            ->create([
                'location_id' => $location->id,
                'date'        => Carbon::now()->subDays(2),
            ]);

        $countOfCreditNotesAfterDate = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditNotesAfterDate)
            ->create([
                'location_id' => $location->id,
                'date'        => Carbon::now()->addDays(2),
            ]);

        $url = action('Finance\CreditNoteListingController@getAll', [
            'date_to' => Carbon::now()->format('Y-m-d'),
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfCreditBeforeDate);
    }

    public function testFiltrationByDateFrom(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $countOfCreditBeforeDate = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditBeforeDate)
            ->create([
                'location_id' => $location->id,
                'date'        => Carbon::now()->subDays(2),
            ]);

        $countOfCreditNotesAfterDate = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditNotesAfterDate)
            ->create([
                'location_id' => $location->id,
                'date'        => Carbon::now()->addDays(2),
            ]);

        $url = action('Finance\CreditNoteListingController@getAll', [
            'date_from' => Carbon::now()->format('Y-m-d'),
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfCreditNotesAfterDate);
    }

    public function testFiltrationByRecipient(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $contactId = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ])->id;

        $countOfCreditWithSameContact = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditWithSameContact)
            ->create([
                'location_id'          => $location->id,
                'recipient_contact_id' => $contactId,
            ]);

        $countOfCreditNotesWithOtherContact = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditNotesWithOtherContact)
            ->create([
                'location_id' => $location->id,
            ]);

        $url = action('Finance\CreditNoteListingController@getAll', [
            'recipient_contact_id' => $contactId,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfCreditWithSameContact);
    }

    public function testFiltrationByJob(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $jobId = factory(Job::class)->create()->id;

        $countOfCreditWithSameJob = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditWithSameJob)
            ->create([
                'location_id' => $location->id,
                'job_id'      => $jobId,
            ]);

        $countOfCreditNotesWithOtherJob = $this->faker->numberBetween(2, 3);
        factory(CreditNote::class, $countOfCreditNotesWithOtherJob)
            ->create([
                'location_id' => $location->id,
            ]);

        $url = action('Finance\CreditNoteListingController@getAll', [
            'job_id' => $jobId,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfCreditWithSameJob);
    }
}
