<?php

namespace Tests\Unit\Reporting\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountingOrganizationLocation;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Reporting\Interfaces\ReportingPaymentsServiceInterface;
use App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Tests\TestCase;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class ReportingPaymentsServiceTest
 *
 * @package Tests\Unit\Reporting\Services
 * @group   invoices
 * @group   finance
 * @group   reporting
 */
class ReportingPaymentsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Reporting\Interfaces\ReportingPaymentsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $models       = [
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganization::class,
            Contact::class,
            InvoiceItem::class,
            InvoiceApproveRequest::class,
            InvoiceStatus::class,
            Invoice::class,
            AccountingOrganizationLocation::class,
            GSCode::class,
            Location::class,
        ];
        $this->models = array_merge($models, $this->models);

        $this->service = Container::getInstance()
            ->make(ReportingPaymentsServiceInterface::class);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderReturnsZeroRecordsWhenSetNoUserIdAndNoLocationId()
    {
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        $attrs    = [
            'location_id' => $location->id,
        ];
        InvoicesTestFactory::createInvoices($count, $attrs, FinancialEntityStatuses::APPROVED);
        $filter = new InvoicePaymentsReportFilter();

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);
        self::assertEquals(0, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderWorksRightWhenSetFilterByLocation()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        $attrs    = [
            'location_id' => $location->id,
        ];
        InvoicesTestFactory::createInvoices($count, $attrs, FinancialEntityStatuses::APPROVED);
        $filter = new InvoicePaymentsReportFilter($attrs);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals($count, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderWorksRightWhenSetFilterByUser()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->locations()->attach($location->id, [
            'primary' => true,
        ]);
        $count = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $filter = new InvoicePaymentsReportFilter([
            'user_id' => $user->id,
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals($count, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderReturnsZeroRecordsWhenNoApprovedInvoices()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        $attrs    = [
            'location_id' => $location->id,
        ];
        InvoicesTestFactory::createInvoices($count, $attrs);
        $filter = new InvoicePaymentsReportFilter($attrs);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals(0, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderWorksRightWhenSetFilterByDate()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        $testDate = $this->faker->date();
        Carbon::setTestNow($testDate);
        InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $filter = new InvoicePaymentsReportFilter([
            'location_id' => $location->id,
            'date_from'   => (new Carbon($testDate))->subMonth(),
            'date_to'     => (new Carbon($testDate))->addMonth(),
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals($count, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderReturnsZeroRecordsWhenFilterByDateFromIsOutOfPeriod()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        $testDate = $this->faker->date();
        Carbon::setTestNow($testDate);
        InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $filter = new InvoicePaymentsReportFilter([
            'location_id' => $location->id,
            'date_from'   => (new Carbon($testDate))->addMonth(),
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals(0, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderReturnsZeroRecordsWhenFilterByDateToIsOutOfPeriod()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        $testDate = $this->faker->date();
        Carbon::setTestNow($testDate);
        InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $filter = new InvoicePaymentsReportFilter([
            'location_id' => $location->id,
            'date_to'     => (new Carbon($testDate))->subMonth(),
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals(0, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderWorksRightWhenSetFilterByRecipientContact()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $count    = $this->faker->numberBetween(1, 3);
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $attrs   = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $contact->id,
        ];
        InvoicesTestFactory::createInvoices($count, $attrs, FinancialEntityStatuses::APPROVED);
        $filter = new InvoicePaymentsReportFilter($attrs);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals($count, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderWorksRightWhenSetFilterByType()
    {
        /** @var Location $location */
        $location   = factory(Location::class)->create();
        $count      = $this->faker->numberBetween(1, 3);
        $invoices   = InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $invoiceIds = $invoices->pluck('id')->toArray();
        $type       = $this->faker->randomElement(PaymentTypes::values());
        InvoicesTestFactory::createPaymentsForInvoices($invoiceIds, $type);
        $filter = new InvoicePaymentsReportFilter([
            'location_id' => $location->id,
            'type'        => $type,
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals($count, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderWorksRightWhenSetFilterByAmount()
    {
        /** @var Location $location */
        $location   = factory(Location::class)->create();
        $count      = $this->faker->numberBetween(1, 3);
        $invoices   = InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $invoiceIds = $invoices->pluck('id')->toArray();
        $minAmount  = $this->faker->numberBetween(10, 20);
        $maxAmount  = $this->faker->numberBetween(90, 100);
        InvoicesTestFactory::createPaymentsForInvoices($invoiceIds, null, $minAmount, $maxAmount);
        $filter = new InvoicePaymentsReportFilter([
            'location_id' => $location->id,
            'amount_from' => $minAmount - 1,
            'amount_to'   => $maxAmount + 1,
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals($count, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderReturnsZeroRecordsWhenFilterByAmountFromIsOutOfDiapason()
    {
        /** @var Location $location */
        $location   = factory(Location::class)->create();
        $count      = $this->faker->numberBetween(1, 3);
        $invoices   = InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $invoiceIds = $invoices->pluck('id')->toArray();
        $minAmount  = $this->faker->numberBetween(10, 20);
        $maxAmount  = $this->faker->numberBetween(90, 100);
        InvoicesTestFactory::createPaymentsForInvoices($invoiceIds, null, $minAmount, $maxAmount);
        $filter = new InvoicePaymentsReportFilter([
            'location_id' => $location->id,
            'amount_from' => $maxAmount + 1,
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals(0, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderReturnsZeroRecordsWhenFilterByAmountToIsOutOfDiapason()
    {
        /** @var Location $location */
        $location   = factory(Location::class)->create();
        $count      = $this->faker->numberBetween(1, 3);
        $invoices   = InvoicesTestFactory::createInvoices($count, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED);
        $invoiceIds = $invoices->pluck('id')->toArray();
        $minAmount  = $this->faker->numberBetween(10, 20);
        $maxAmount  = $this->faker->numberBetween(90, 100);
        InvoicesTestFactory::createPaymentsForInvoices($invoiceIds, null, $minAmount, $maxAmount);
        $filter = new InvoicePaymentsReportFilter([
            'location_id' => $location->id,
            'amount_to'   => $minAmount - 1,
        ]);

        $reportBuilder = $this->service->getInvoicePaymentsReportBuilder($filter);

        self::assertEquals(0, $reportBuilder->count());
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetInvoicePaymentsReportBuilderOrderedByIdDesc()
    {
        /** @var Location $location */
        $location         = factory(Location::class)->create();
        $count            = $this->faker->numberBetween(1, 3);
        $attrs            = [
            'location_id' => $location->id,
        ];
        $invoices         = InvoicesTestFactory::createInvoices($count, $attrs, FinancialEntityStatuses::APPROVED);
        $sortedInvoiceIds = $invoices->sortByDesc('id')->pluck('id')->toArray();
        $filter           = new InvoicePaymentsReportFilter($attrs);

        $reportBuilder   = $this->service->getInvoicePaymentsReportBuilder($filter);
        $sortedReportIds = $reportBuilder->pluck('id')->toArray();

        self::assertTrue($sortedInvoiceIds === $sortedReportIds);
    }
}
