<?php

namespace Tests\API\Finance;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Documents\Models\Document;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Events\AddApproveRequestsToCreditNote;
use App\Components\Finance\Events\CreditNoteApproved;
use App\Components\Finance\Events\CreditNoteCreated;
use App\Components\Finance\Events\CreditNoteDeleted;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Resources\CreditNoteResource;
use App\Components\Finance\Services\CreditNoteService;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Components\Notes\Models\Note;
use App\Components\Tags\Models\Tag;
use App\Helpers\Decimal;
use App\Http\Responses\Finance\ApproverListResponse;
use App\Http\Responses\Notes\FullNoteListResponse;
use App\Jobs\Finance\RecalculateCounters;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class CreditNotesControllerTest
 *
 * @package Tests\API\Finance
 *
 * @group   finance
 * @group   credit-note
 */
class CreditNotesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.credit_notes.manage',
        'finance.credit_notes.view',
        'finance.credit_notes.manage_locked',
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
    private $receivableAccount;
    /**
     * @var GLAccount
     */
    private $salesAccount;
    /**
     * @var GLAccount
     */
    private $taxAccount;

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
        $assetsAccountType         = factory(AccountType::class)->create([
            'name'                     => 'Asset',
            'increase_action_is_debit' => true,
        ]);
        $revenueAccountType        = factory(AccountType::class)->create([
            'name'                     => 'Revenue',
            'increase_action_is_debit' => false,
        ]);
        $liabilityAccountType      = factory(AccountType::class)->create([
            'name'                     => 'Liability',
            'increase_action_is_debit' => false,
        ]);
        $this->receivableAccount   = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'account_type_id'            => $assetsAccountType->id,
        ]);
        $this->salesAccount        = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'account_type_id'            => $revenueAccountType->id,
        ]);
        $this->taxAccount          = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'account_type_id'            => $liabilityAccountType->id,
        ]);
        $this->accountOrganization->update([
            'accounts_receivable_account_id' => $this->receivableAccount->id,
            'tax_payable_account_id'         => $this->taxAccount->id,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateCreditNote(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location);
        $paymentDetailsAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        $accountingOrganization->payment_details_account_id = $paymentDetailsAccount->id;
        $accountingOrganization->saveOrFail();

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        /** @var GLAccount $bankAccount */
        $bankAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'name'                     => 'Asset',
                'increase_action_is_debit' => true,
            ])->id,
        ]);

        $data = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'job_id'               => factory(Job::class)->create()->id,
            'payment_id'           => factory(Payment::class)->create()->id,
            'date'                 => Carbon::now()->toDateString(),
            'items'                => [
                [
                    'gs_code_id'    => factory(GSCode::class)->create()->id,
                    'description'   => $this->faker->word,
                    'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
                    'quantity'      => $this->faker->numberBetween(1, 4),
                    'gl_account_id' => $bankAccount->id,
                    'tax_rate_id'   => $bankAccount->tax_rate_id,
                ],
            ],
        ];

        $this->expectsEvents(CreditNoteCreated::class);

        $url      = action('Finance\CreditNotesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $model = CreditNote::findOrFail($response->getData('id'));

        self::assertEquals($model->location_id, $data['location_id']);
        self::assertEquals($model->recipient_contact_id, $data['recipient_contact_id']);
        self::assertEquals($address->full_address, $model->recipient_address);
        self::assertEquals($recipientContact->getContactName(), $model->recipient_name);
        self::assertEquals($model->job_id, $data['job_id']);
        self::assertEquals($model->payment_id, $data['payment_id']);
        self::assertEquals($model->date->toDateString(), $data['date']);
        self::assertCount(1, $model->items);
    }

    /**
     * @throws \Throwable
     */
    public function testRecalculateJobShouldBeDispatchedAfterCreateCreditNote(): void
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location);
        $paymentDetailsAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        $accountingOrganization->payment_details_account_id = $paymentDetailsAccount->id;
        $accountingOrganization->saveOrFail();

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'job_id'               => factory(Job::class)->create()->id,
            'payment_id'           => factory(Payment::class)->create()->id,
            'date'                 => Carbon::now()->toDateString(),
        ];

        $this->expectsJobs(RecalculateCounters::class);
        $url      = action('Finance\CreditNotesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);
    }

    public function testShowCreditNote(): void
    {
        $model = factory(CreditNote::class)->create();
        $url   = action('Finance\CreditNotesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $data     = $response->getData();

        $response->assertStatus(200)
            ->assertValidSchema(CreditNoteResource::class);
        self::assertEquals($data['id'], $model->id);
    }

    public function testUpdateCreditNote(): void
    {
        /** @var CreditNote $model */
        $model = factory(CreditNote::class)->create();
        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = [
            'location_id'          => factory(Location::class)->create()->id,
            'recipient_contact_id' => $recipientContact->id,
            'job_id'               => factory(Job::class)->create()->id,
            'document_id'          => factory(Document::class)->create()->id,
            'payment_id'           => factory(Payment::class)->create()->id,
            'date'                 => Carbon::now()->addDays($this->faker->numberBetween(10, 20))->toDateString(),
        ];

        $url = action('Finance\CreditNotesController@update', [
            'id' => $model->id,
        ]);
        $this->expectsJobs(RecalculateCounters::class);
        $this->patchJson($url, $data)
            ->assertStatus(200);

        $creditNote = CreditNote::find($model->id);

        //Assert that location id was not changed
        self::assertEquals($model->location_id, $creditNote->location_id);
        //Assert that document id was not changed
        self::assertEquals($model->document_id, $creditNote->document_id);
        self::assertEquals($creditNote->job_id, $data['job_id']);
        self::assertEquals($creditNote->payment_id, $data['payment_id']);
        self::assertEquals($creditNote->date->toDateString(), $data['date']);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateLockedCreditNote(): void
    {
        $location = factory(Location::class)->create();
        $model    = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
            'locked_at'                  => Carbon::now(),
        ]);
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $model->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);
        $user = factory(User::class)->create([
            'credit_note_approval_limit' => $model->getTotalAmount() + 1,
        ]);
        $user->locations()->attach($location);

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = [
            'recipient_contact_id' => $recipientContact->id,
            'job_id'               => factory(Job::class)->create()->id,
            'date'                 => Carbon::now()->addDays($this->faker->numberBetween(10, 20))->toDateString(),
        ];

        $url = action('Finance\CreditNotesController@update', [
            'id' => $model->id,
        ]);
        $this->expectsJobs(RecalculateCounters::class);
        $this->patchJson($url, $data)
            ->assertStatus(200);

        $creditNote = CreditNote::find($model->id);

        self::assertEquals($creditNote->recipient_contact_id, $data['recipient_contact_id']);
        self::assertEquals($creditNote->job_id, $data['job_id']);
        self::assertEquals($creditNote->date->toDateString(), $data['date']);
    }

    public function testUpdateCreditNoteShouldReturnNotAllowedError(): void
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->day,
        ]);
        /** @var CreditNote $model */
        $model = factory(CreditNote::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);

        $data = [
            'date' => Carbon::now()->subDays(1)->toDateString(),
        ];

        $url = action('Finance\CreditNotesController@update', [
            'id' => $model->id,
        ]);
        $this->patchJson($url, $data)
            ->assertNotAllowed('Selected date is earlier than end-of-month financial date.');
    }

    /**
     * @throws \Exception
     */
    public function testDeleteCreditNote(): void
    {
        $model = factory(CreditNote::class)->create();
        $url   = action('Finance\CreditNotesController@destroy', [
            'id' => $model->id,
        ]);

        $this->expectsEvents(CreditNoteDeleted::class);
        $response = $this->deleteJson($url);
        $response->assertStatus(200);
        self::assertNull(CreditNote::find($model->id));
    }

    /**
     * @throws \Exception
     */
    public function testRecalculateJobShouldBeDispatchedAfterDeleteCreditNote(): void
    {
        $model = factory(CreditNote::class)->create();
        $url   = action('Finance\CreditNotesController@destroy', [
            'id' => $model->id,
        ]);

        $this->expectsJobs(RecalculateCounters::class);
        $response = $this->deleteJson($url);
        $response->assertStatus(200);
        self::assertNull(CreditNote::find($model->id));
    }

    public function testGetCreditNoteNotes(): void
    {
        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create();
        $count      = $this->faker->numberBetween(1, 5);
        $notes      = factory(Note::class, $count)->create();
        foreach ($notes as $note) {
            $creditNote->notes()->attach($note->id);
        }
        $url = action('Finance\CreditNoteNotesController@getNotes', ['credit_note' => $creditNote->id]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(FullNoteListResponse::class, true)
            ->assertJsonCount($count, 'data');
    }

    public function testGetCreditNoteTags(): void
    {
        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create();
        $count      = $this->faker->numberBetween(1, 5);
        $notes      = factory(Tag::class, $count)->create();
        foreach ($notes as $note) {
            $creditNote->tags()->attach($note->id);
        }
        $url = action('Finance\CreditNoteTagsController@getTags', ['credit_note' => $creditNote->id]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    /**
     * @throws \Exception
     */
    public function testApproveCreditNote(): void
    {
        $location = factory(Location::class)->create();
        $model    = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $model->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);
        $this->user->update(['credit_note_approval_limit' => $model->getTotalAmount() + 1]);
        $this->user->locations()->attach($location);
        $url = action('Finance\CreditNotesController@approve', [
            'id' => $model->id,
        ]);
        $this->actingAs($this->user);

        $this->expectsEvents(CreditNoteApproved::class);
        $response = $this->postJson($url);
        $response->assertStatus(200);

        self::assertEquals($model->fresh()->getCurrentStatus(), FinancialEntityStatuses::APPROVED);
    }

    /**
     * @throws \Exception
     */
    public function testRecalculateJobShouldBeDispatchedAfterApproveCreditNote(): void
    {
        $location = factory(Location::class)->create();
        $model    = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $model->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);
        $this->user->update(['credit_note_approval_limit' => $model->getTotalAmount() + 1]);
        $this->user->locations()->attach($location);
        $url = action('Finance\CreditNotesController@approve', [
            'id' => $model->id,
        ]);
        $this->actingAs($this->user);

        $this->expectsJobs(RecalculateCounters::class);
        $this->postJson($url);
    }

    /**
     * @throws \Throwable
     */
    public function testGetCreditNoteApproveRequests(): void
    {
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create([
            'location_id' => $location->id,
        ]);
        factory(User::class, $count)
            ->create([
                'credit_note_approval_limit' => $creditNote->getTotalAmount(),
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });

        $this->service->createApproveRequest(
            $creditNote->id,
            factory(User::class)->create()->id,
            $this->service->getApproversList($creditNote->id)
                ->pluck('id')
                ->toArray()
        );

        $url = action('Finance\CreditNotesController@getApproveRequests', [
            'id' => $creditNote->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    /**
     * @throws \Throwable
     */
    public function testAddApproveRequestsToCreditNote(): void
    {
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);

        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create([
            'location_id' => $location->id,
        ]);

        factory(User::class, $count)
            ->create([
                'credit_note_approval_limit' => $creditNote->getTotalAmount() + 1,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });

        $data = [
            'approver_list' => $this->service->getApproversList($creditNote->id)
                ->map(function ($approver) {
                    return $approver['id'];
                })
                ->toArray(),
        ];

        $this->expectsEvents(AddApproveRequestsToCreditNote::class);
        $url = action('Finance\CreditNotesController@addApproveRequests', [
            'id' => $creditNote->id,
        ]);

        $response = $this->postJson($url, $data);
        $response->assertStatus(200);

        $creditNote->fresh();
        self::assertEquals($creditNote->approveRequests()->count(), $count);
    }

    /**
     * @throws \Throwable
     */
    public function testRecalculateJobShouldBeDispatchedAfterAddApproveRequestsToCreditNote(): void
    {
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);

        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create([
            'location_id' => $location->id,
        ]);

        factory(User::class, $count)
            ->create([
                'credit_note_approval_limit' => $creditNote->getTotalAmount() + 1,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });

        $data = [
            'approver_list' => $this->service->getApproversList($creditNote->id)
                ->map(function ($approver) {
                    return $approver['id'];
                })
                ->toArray(),
        ];

        $this->expectsJobs(RecalculateCounters::class);
        $url = action('Finance\CreditNotesController@addApproveRequests', [
            'id' => $creditNote->id,
        ]);

        $this->postJson($url, $data);
    }

    public function testCreatePaymentForCreditNote(): void
    {
        $location = factory(Location::class)->create();
        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        $amount     = $this->faker->randomFloat(2, 100, 1000);
        factory(CreditNoteItem::class)->create(
            [
                'credit_note_id' => $creditNote->id,
                'gl_account_id'  => $this->salesAccount->id,
                'unit_cost'      => $amount,
                'quantity'       => 1,

            ]
        );
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $url = action('Finance\CreditNotesController@createPayment', [
            'id' => $creditNote->id,
        ]);

        $data = [
            'payment_items' => [
                ['invoice_id' => $invoice->id, 'amount' => $invoice->getSubTotalAmount()],
            ],
        ];

        $this->postJson($url, $data)
            ->assertStatus(200);

        $paymentAmount        = Payment::find($creditNote->fresh()->payment_id)->amount;
        $invoicePaymentAmount = InvoicePayment::query()
            ->where(['invoice_id' => $invoice->id])
            ->first()
            ->amount;
        self::assertTrue(Decimal::areEquals(round($creditNote->getTotalAmount(), 2), $paymentAmount));
        self::assertTrue(Decimal::areEquals($amount, $invoicePaymentAmount));
    }

    public function testGetApproverList(): void
    {
        $amount = $this->faker->randomFloat(2, 100, 1000);
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create([
            'location_id' => $location->id,
        ]);
        factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'unit_cost'      => $amount,
            'quantity'       => 1,
        ]);

        $countOfApprover = $this->faker->numberBetween(3, 5);
        factory(User::class, $countOfApprover)
            ->create([
                'credit_note_approval_limit' => $amount + 1,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });
        factory(User::class, $countOfApprover)
            ->create([
                'credit_note_approval_limit' => $amount + 1,
            ]);
        factory(User::class, $countOfApprover)
            ->create([
                'credit_note_approval_limit' => $amount - 1,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });

        $url = action('Finance\CreditNotesController@approverList', ['id' => $creditNote->id]);

        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertJsonDataCount($countOfApprover)
            ->assertValidSchema(ApproverListResponse::class, true);
    }
}
