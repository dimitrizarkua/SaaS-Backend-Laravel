<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteApproveRequest;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\CreditNoteStatus;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\CreateCreditNoteData;
use App\Components\Finance\Services\CreditNoteService;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Class CreditNoteServiceTest
 *
 * @package App\Components\Finance\Services
 * @group   finance
 * @group   credit-note
 */
class CreditNoteServiceTest extends TestCase
{
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

        $this->models = array_merge([
            CreditNoteItem::class,
            CreditNoteApproveRequest::class,
            CreditNoteStatus::class,
            CreditNote::class,

            AccountingOrganization::class,
        ], $this->models);

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
        $this->accountOrganization->update(
            [
                'accounts_receivable_account_id' => $this->receivableAccount->id,
                'tax_payable_account_id'         => $this->taxAccount->id,
            ]
        );

        $this->service = $this->app->get(CreditNoteService::class);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testCreateCreditNoteSuccess(): void
    {
        $location = factory(Location::class)->create();
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

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = new CreateCreditNoteData([
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'job_id'               => factory(Job::class)->create()->id,
            'payment_id'           => factory(Payment::class)->create()->id,
            'date'                 => Carbon::now(),
        ]);
        $user = factory(User::class)->create();
        /** @var CreditNote $creditNote */
        $creditNote = $this->service->create($data, $user->id);

        self::assertEquals($creditNote->getCurrentStatus(), FinancialEntityStatuses::DRAFT);
        self::assertEquals($creditNote->date, $data->date);
        self::assertEquals($creditNote->location_id, $data->location_id);
        self::assertEquals($accountingOrganization->id, $creditNote->accounting_organization_id);
        self::assertEquals($data->recipient_contact_id, $creditNote->recipient_contact_id);
        self::assertEquals($data->job_id, $creditNote->job_id);
        self::assertEquals($data->payment_id, $creditNote->payment_id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToCreateCreditNoteWithNoActiveAccountingOrganization(): void
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'is_active' => false,
        ]);
        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = new CreateCreditNoteData([
            'location_id'                => factory(Location::class)->create()->id,
            'accounting_organization_id' => $accountingOrganization->id,
            'recipient_contact_id'       => $recipientContact->id,
            'date'                       => Carbon::now()->subDays($this->faker->numberBetween(40, 60)),
        ]);
        $user = factory(User::class)->create();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('For given location there is no any active Accounting Organization.');
        $this->service->create($data, $user->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testFailToCreateCreditNoteWithInvalidDate(): void
    {
        $location = factory(Location::class)->create();
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'is_active' => true,
        ]);
        $accountingOrganization->locations()->attach($location->id);
        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        $data = new CreateCreditNoteData([
            'location_id'                => $location->id,
            'accounting_organization_id' => $accountingOrganization->id,
            'recipient_contact_id'       => $recipientContact->id,
            'date'                       => Carbon::now()->subDays($this->faker->numberBetween(40, 60)),
        ]);
        $user = factory(User::class)->create();

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Credit note can only be created if it\'s date is after the end-of-month financial date.'
        );
        $this->service->create($data, $user->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateCreditNoteSuccess(): void
    {
        $creditNote = factory(CreditNote::class)->create();
        $date       = Carbon::now()->addDays($this->faker->numberBetween(10, 20));

        $data = [
            'date' => $date,
        ];

        $updatedCreditNote = $this->service->update($creditNote->id, $data);

        self::assertEquals($updatedCreditNote->date, $date);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateCreditNoteWithInvalidDate(): void
    {
        $creditNote = factory(CreditNote::class)->create();
        $date       = Carbon::now()->subDays($this->faker->numberBetween(40, 60));
        $data       = [
            'date' => $date,
        ];

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(
            'Selected date is earlier than end-of-month financial date.'
        );
        $this->service->update($creditNote->id, $data);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateCreditNoteWithInvalidStatus(): void
    {
        $location   = factory(Location::class)->create();
        $creditNote = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
            'locked_at'                  => Carbon::now(),
        ]);
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $creditNote->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);
        $user = factory(User::class)->create(['credit_note_approval_limit' => $creditNote->getTotalAmount()]);
        $user->locations()->attach($location);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('You can\'t update the credit note because it has been locked.');
        $this->service->update($creditNote->id, []);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateCreditNoteSuccessWithInvalidStatusAndNonValidate(): void
    {
        $location   = factory(Location::class)->create();
        $creditNote = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
            'locked_at'                  => \Carbon\Carbon::now(),

        ]);
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $creditNote->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);
        $user = factory(User::class)->create(['credit_note_approval_limit' => $creditNote->getTotalAmount()]);
        $date = Carbon::now()->addDays($this->faker->numberBetween(10, 20));
        $user->locations()->attach($location);
        $data = ['date' => $date];

        $updatedCreditNote = $this->service->update($creditNote->id, $data, true);

        self::assertEquals($updatedCreditNote->date, $date);
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteCreditNoteSuccess(): void
    {
        $location   = factory(Location::class)->create();
        $creditNote = factory(CreditNote::class)->create(['location_id' => $location->id]);
        factory(CreditNoteItem::class, 3)->create(['credit_note_id' => $creditNote->id]);
        $user = factory(User::class)->create(['credit_note_approval_limit' => $creditNote->getTotalAmount()]);
        $user->locations()->attach($location);

        $this->service->delete($creditNote->id);
        self::assertNull(CreditNote::find($creditNote->id));
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDeleteCreditNoteWithInvalidStatus(): void
    {
        $location   = factory(Location::class)->create();
        $creditNote = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $creditNote->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);
        $user = factory(User::class)->create([
            'credit_note_approval_limit' => $creditNote->getTotalAmount(),
        ]);
        $user->locations()->attach($location);
        $this->service->approve($creditNote->id, $user);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('You can\'t delete the credit note because it has already been approved.');
        $this->service->delete($creditNote->id);
    }

    /**
     * @throws \Throwable
     */
    public function testApproveCreditNoteSuccess(): void
    {
        $location = factory(Location::class)->create();

        $creditNote = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $creditNote->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);
        $user = factory(User::class)->create([
            'credit_note_approval_limit' => $creditNote->getTotalAmount(),
        ]);
        $user->locations()->attach($location);

        $this->service->approve($creditNote->id, $user);

        self::assertEquals($creditNote->getCurrentStatus(), FinancialEntityStatuses::APPROVED);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToApproveCreditNoteWithTotalAmountGreaterThenUserLimit(): void
    {
        $creditNote = factory(CreditNote::class)->create();
        factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $creditNote->id,
        ]);
        $user = factory(User::class)->create([
            'credit_note_approval_limit' => $creditNote->getTotalAmount() - 1,
        ]);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage(sprintf(
            'User [%d] can\'t be an approver of credit note [%d].',
            $user->id,
            $creditNote->id
        ));
        $this->service->approve($creditNote->id, $user);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToApproveCreditNoteWithZeroBalance(): void
    {
        $location = factory(Location::class)->create();

        $creditNote = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        $user       = factory(User::class)->create([
            'credit_note_approval_limit' => $creditNote->getTotalAmount(),
        ]);
        $user->locations()->attach($location);

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionMessage('Unable to approve credit note with zero balance.');
        $this->service->approve($creditNote->id, $user);
    }
}
