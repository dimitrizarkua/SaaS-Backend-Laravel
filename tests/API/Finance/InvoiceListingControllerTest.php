<?php

namespace Tests\API\Finance;

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\InvoicePaymentType;
use App\Components\Finance\Enums\InvoiceVirtualStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Http\Responses\Finance\InvoicesInfoResponse;
use App\Http\Responses\Finance\InvoicesListResponse;
use App\Models\User;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class InvoiceListingControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 * @group   invoices
 * @group   forwarded-payments
 * @group   finance-listings
 */
class InvoiceListingControllerTest extends ApiTestCase
{
    protected $permissions = ['finance.invoices.view'];

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
            ForwardedPayment::class,
            ForwardedPaymentInvoice::class,
        ], $this->models);
    }

    public function testInvoiceListingInfoMethod(): void
    {
        $draftInvoiceCount    = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesCount  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $this->user,
            $draftInvoiceCount,
            $unpaidInvoicesCount,
            $overdueInvoicesCount
        );

        $url      = action('Finance\InvoiceListingController@info');
        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(InvoicesInfoResponse::class, true);

        self::assertEquals($draftInvoiceCount, $response->getData('draft')['count']);
        self::assertEquals($unpaidInvoicesCount, $response->getData('unpaid')['count']);
        self::assertEquals($overdueInvoicesCount, $response->getData('overdue')['count']);
    }

    public function testInvoiceListingInfoMethodWithLocationFilter(): void
    {
        $firstLocation                   = factory(Location::class)->create();
        $draftInvoiceForFirstLocation    = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesFotFirstLocation  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesForFirstLocation = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $this->user,
            $draftInvoiceForFirstLocation,
            $unpaidInvoicesFotFirstLocation,
            $overdueInvoicesForFirstLocation,
            $firstLocation
        );

        $secondLocation                   = factory(Location::class)->create();
        $draftInvoiceForSecondLocation    = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesFotSecondLocation  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesForSecondLocation = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $this->user,
            $draftInvoiceForSecondLocation,
            $unpaidInvoicesFotSecondLocation,
            $overdueInvoicesForSecondLocation,
            $secondLocation
        );

        $url = action('Finance\InvoiceListingController@info', [
            'locations' => [$secondLocation->id],
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(InvoicesInfoResponse::class, true);

        self::assertEquals($draftInvoiceForSecondLocation, $response->getData('draft')['count']);
        self::assertEquals($unpaidInvoicesFotSecondLocation, $response->getData('unpaid')['count']);
        self::assertEquals($overdueInvoicesForSecondLocation, $response->getData('overdue')['count']);
    }

    public function testInvoiceListingDraftMethod(): void
    {
        $draftCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices($this->user, $draftCount);

        $url      = action('Finance\InvoiceListingController@draft');
        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($draftCount)
            ->assertValidSchema(InvoicesListResponse::class, true);

        foreach ($response->getData() as $item) {
            self::assertEquals(InvoiceVirtualStatuses::DRAFT, $item['virtual_status']);
        }
    }

    public function testInvoiceListingUnpaidMethod(): void
    {
        $overDue             = $this->faker->numberBetween(1, 3);
        $unpaidInvoicesCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $this->user,
            0,
            $unpaidInvoicesCount,
            $overDue
        );

        $url      = action('Finance\InvoiceListingController@unpaid');
        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($unpaidInvoicesCount)
            ->assertValidSchema(InvoicesListResponse::class, true);

        foreach ($response->getData() as $item) {
            self::assertEquals(InvoiceVirtualStatuses::UNPAID, $item['virtual_status']);
        }
    }

    public function testInvoiceListingOverdueMethod(): void
    {
        $unpaidInvoicesCount  = $this->faker->numberBetween(1, 3);
        $overdueInvoicesCount = $this->faker->numberBetween(1, 3);
        InvoicesTestFactory::createListOfInvoices(
            $this->user,
            0,
            $unpaidInvoicesCount,
            $overdueInvoicesCount
        );

        $url      = action('Finance\InvoiceListingController@overdue');
        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($overdueInvoicesCount)
            ->assertValidSchema(InvoicesListResponse::class, true);

        foreach ($response->getData() as $item) {
            self::assertEquals(InvoiceVirtualStatuses::OVERDUE, $item['virtual_status']);
        }
    }

    public function testInvoiceListingUnforwardedMethod(): void
    {
        $unforwardedInvoicesCount = $this->faker->numberBetween(2, 3);

        $invoicesList = InvoicesTestFactory::createListOfInvoices(
            $this->user,
            0,
            0,
            0,
            null,
            $unforwardedInvoicesCount
        );

        /** @var \Illuminate\Support\Collection $unforwarded */
        $unforwarded = $invoicesList[InvoicePaymentType::FORWARDED];
        $url         = action('Finance\InvoiceListingController@listUnforwarded', [
            'location_id' => $unforwarded->first()->location_id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($unforwardedInvoicesCount)
            ->assertValidSchema(InvoicesListResponse::class, true);
    }
}
