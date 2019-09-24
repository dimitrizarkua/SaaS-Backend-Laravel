<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Interfaces\InvoiceListingServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\Filters\InvoiceListingFilter;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class InvoiceListingServiceTest
 *
 * @package Tests\Unit\Finance\Services
 * @group   finance
 * @group   invoices
 */
class InvoiceListingServiceTest extends TestCase
{
    /**
     * @var InvoiceListingServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->models = array_merge([
            InvoicePayment::class,
            Payment::class,
            LocationUser::class,
            Location::class,
            User::class,
            InvoiceItem::class,
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganization::class,
            Contact::class,
            Address::class,
            Suburb::class,
            State::class,
            Country::class,

            InvoiceApproveRequest::class,
            InvoiceStatus::class,
            Invoice::class,
        ], $this->models);

        $this->service = $this->app->make(InvoiceListingServiceInterface::class);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testShouldReturnDraftInvoicesForGivenLocations(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $draftCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices($user, $draftCount);

        $filter = new InvoiceListingFilter(['user_id' => $user->id]);
        $draft  = $this->service->getDraftInvoicesList($filter);
        self::assertCount($draftCount, $draft);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testShouldReturnUnpaidInvoices(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $overDueInvoicesCount = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesCount  = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $user,
            0,
            $unpaidInvoicesCount,
            $overDueInvoicesCount
        );

        $filter         = new InvoiceListingFilter(['user_id' => $user->id]);
        $unpaidInvoices = $this->service->getUnpaidInvoicesList($filter);
        self::assertCount($unpaidInvoicesCount, $unpaidInvoices);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testShouldReturnCorrectCountOfOverDueInvoice(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $overdueCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices($user, 0, 0, $overdueCount);

        $filter  = new InvoiceListingFilter(['user_id' => $user->id]);
        $overdue = $this->service->getOverdueInvoicesList($filter);
        self::assertCount($overdueCount, $overdue);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCountersMethod(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $location             = factory(Location::class)->create();
        $draftInvoiceCount    = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesCount  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $user,
            $draftInvoiceCount,
            $unpaidInvoicesCount,
            $overdueInvoicesCount,
            $location
        );

        $filter        = new InvoiceListingFilter(['user_id' => $user->id]);
        $counters      = $this->service->getInvoiceCounters([$location->id]);
        $reducer       = function (float $total, Invoice $invoice) {
            return $total + $invoice->getAmountDue();
        };
        $draftAmount   = $this->service->getDraftInvoicesList($filter)->reduce($reducer, 0);
        $unpaidAmount  = $this->service->getUnpaidInvoicesList($filter)->reduce($reducer, 0);
        $overdueAmount = $this->service->getOverdueInvoicesList($filter)->reduce($reducer, 0);
        self::assertEquals($counters['draft']['count'], $draftInvoiceCount);
        self::assertEquals($counters['draft']['amount'], $draftAmount);
        self::assertEquals($counters['unpaid']['count'], $unpaidInvoicesCount);
        self::assertEquals($counters['unpaid']['amount'], $unpaidAmount);
        self::assertEquals($counters['overdue']['count'], $overdueInvoicesCount);
        self::assertEquals($counters['overdue']['amount'], $overdueAmount);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testTestInvoicesFiltrationByJob(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $draftInvoiceCount    = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesCount  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $user,
            $draftInvoiceCount,
            $unpaidInvoicesCount,
            $overdueInvoicesCount
        );

        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create([
            'job_id'      => $job->id,
            'location_id' => $user->locations->first()->id,
        ]);

        $filter = new InvoiceListingFilter([
            'user_id' => $user->id,
            'job_id'  => $job->id,
        ]);
        $result = $this->service->getAllInvoicesList($filter)->get();
        self::assertCount(1, $result);
        self::assertEquals($invoice->id, $result->first()->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testTestInvoicesFiltrationByContactId(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $draftInvoiceCount    = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesCount  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $user,
            $draftInvoiceCount,
            $unpaidInvoicesCount,
            $overdueInvoicesCount
        );

        $contact = factory(Contact::class)->create();
        $invoice = factory(Invoice::class)->create([
            'recipient_contact_id' => $contact->id,
            'location_id'          => $user->locations->first()->id,
        ]);

        $filter = new InvoiceListingFilter([
            'user_id'              => $user->id,
            'recipient_contact_id' => $contact->id,
        ]);
        $result = $this->service->getAllInvoicesList($filter)->get();
        self::assertCount(1, $result);
        self::assertEquals($invoice->id, $result->first()->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testTestInvoicesFiltrationByDueDate(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $draftInvoiceCount    = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesCount  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $user,
            $draftInvoiceCount,
            $unpaidInvoicesCount,
            $overdueInvoicesCount
        );

        $dueAt = Carbon::now();

        $invoice = factory(Invoice::class)->create([
            'due_at'      => $dueAt,
            'location_id' => $user->locations->first()->id,
        ]);

        $filter = new InvoiceListingFilter([
            'user_id'       => $user->id,
            'due_date_from' => $dueAt->subDays(1),
            'due_date_to'   => $dueAt->addDays(1),
        ]);

        $result = $this->service->getAllInvoicesList($filter)->get();
        self::assertCount(1, $result);
        self::assertEquals($invoice->id, $result->first()->id);
    }
}
