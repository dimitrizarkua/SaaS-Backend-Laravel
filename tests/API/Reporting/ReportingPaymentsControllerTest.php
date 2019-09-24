<?php

namespace Tests\API\Reporting;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Enums\FinancialEntityStatuses;
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
use App\Models\User;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class ReportingPaymentsControllerTest
 *
 * @package Tests\API\Reporting
 * @group   invoices
 * @group   finance
 * @group   reporting
 */
class ReportingPaymentsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.invoices.reports.view',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $models       = [
            Location::class,
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
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testInvoicePaymentsReport(): void
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location->id);
        $filter         = [
            'location_id' => $location->id,
        ];
        $countOfRecords = $this->faker->numberBetween(1, 5);
        InvoicesTestFactory::createInvoices($countOfRecords, $filter, FinancialEntityStatuses::APPROVED);

        $url = action('Reporting\ReportingPaymentsController@invoicePaymentsReport', $filter);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testInvoicePaymentsReportShouldReturnValidationErrorWhenUserBelongsToOtherLocation(): void
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var Location $otherLocation */
        $otherLocation = factory(Location::class)->create();
        $this->user->locations()->attach($otherLocation->id);
        $filter = [
            'location_id' => $location->id,
        ];

        $url = action('Reporting\ReportingPaymentsController@invoicePaymentsReport', $filter);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testInvoicePaymentsReportShouldReturnValidationErrorWhenLocationIsNotExists(): void
    {
        $filter = [
            'location_id' => 0,
        ];

        $url = action('Reporting\ReportingPaymentsController@invoicePaymentsReport', $filter);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testInvoicePaymentsReportShouldReturnForbiddenError(): void
    {
        $userWithoutPermissions = factory(User::class)->create();
        $this->actingAs($userWithoutPermissions);

        $url = action('Reporting\ReportingPaymentsController@invoicePaymentsReport');

        $this->getJson($url)
            ->assertStatus(403);
    }
}
