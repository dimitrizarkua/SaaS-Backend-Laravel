<?php

namespace Tests\API\Finance;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\InvoicesTestFactory;

/**
 * Class InvoiceApproveRequestsControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 * @group   invoices
 */
class InvoiceApproveRequestsControllerTest extends ApiTestCase
{
    public $permissions = [
        'finance.invoices.view',
        'finance.invoices.manage',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->models = array_merge([
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
        ], $this->models);
    }

    public function testApproveRequestsShouldBeCreated()
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
        ]);
        $invoiceApprovalLimit   = $this->faker->randomFloat(2, 10, 100);
        /** @var Location $location */
        $location        = factory(Location::class)->create();
        $countOfApprover = $this->faker->numberBetween(2, 4);
        /** @var \Illuminate\Support\Collection $approverList */
        $approverList = factory(User::class, $countOfApprover)
            ->create([
                'invoice_approve_limit' => $invoiceApprovalLimit,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });

        $invoice = InvoicesTestFactory::createDraftInvoice([
            'accounting_organization_id' => $accountingOrganization->id,
            'location_id'                => $location->id,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id'  => $invoice->id,
            'unit_cost'   => $invoiceApprovalLimit - 1,
            'quantity'    => 1,
            'discount'    => 0,
            'tax_rate_id' => factory(TaxRate::class)->create(['rate' => 0])->id,
        ]);

        $approverIdsList = $approverList->pluck('id')
            ->toArray();

        $url = action('Finance\InvoiceApproveRequestsController@createApproveRequest', ['invoice_id' => $invoice->id]);

        $this->postJson($url, [
            'approver_list' => $approverIdsList,
        ])
            ->assertStatus(200);
    }

    public function testNotAllowedResponseShouldBeReturnedWhileCreatingApproveRequest()
    {
        $invoiceApprovalLimit = $this->faker->randomFloat(2, 10, 100);
        /** @var Location $location */
        $location        = factory(Location::class)->create();
        $anotherLocation = factory(Location::class)->create();

        /** @var User $approver */
        $approver = factory(User::class)->create([
            'invoice_approve_limit' => $invoiceApprovalLimit,
        ]);
        $approver->locations()->attach($anotherLocation);

        $invoice = InvoicesTestFactory::createDraftInvoice([
            'location_id' => $location->id,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $invoiceApprovalLimit - 1,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $url = action('Finance\InvoiceApproveRequestsController@createApproveRequest', ['invoice_id' => $invoice->id]);

        $this->postJson($url, [
            'approver_list' => [$approver->id],
        ])
            ->assertNotAllowed();
    }

    public function testGetApproveRequestMethod()
    {
        $invoice         = factory(Invoice::class)->create();
        $countOfRequests = $this->faker->numberBetween(2, 4);
        factory(InvoiceApproveRequest::class, $countOfRequests)->create([
            'invoice_id' => $invoice->id,
        ]);

        $url = action('Finance\InvoiceApproveRequestsController@getApproveRequests', [
            'id' => $invoice->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfRequests);
    }

    public function testGetApproverList()
    {
        $amount = $this->faker->randomFloat(2, 100, 1000);
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'location_id' => $location->id,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id'  => $invoice->id,
            'unit_cost'   => $amount,
            'quantity'    => 1,
            'discount'    => 0,
            'tax_rate_id' => factory(TaxRate::class)->create(['rate' => 0])->id,
        ]);

        $countOfApprover = $this->faker->numberBetween(3, 5);
        factory(User::class, $countOfApprover)
            ->create([
                'invoice_approve_limit' => $amount + 1,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });
        factory(User::class, $countOfApprover)
            ->create([
                'invoice_approve_limit' => $amount + 1,
            ]);
        factory(User::class, $countOfApprover)
            ->create([
                'invoice_approve_limit' => $amount - 1,
            ])
            ->each(function (User $user) use ($location) {
                $user->locations()->attach($location);
            });

        $url = action('Finance\InvoiceApproveRequestsController@approverList', ['id' => $invoice->id]);

        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertJsonDataCount($countOfApprover);
    }
}
