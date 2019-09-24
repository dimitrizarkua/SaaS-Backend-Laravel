<?php

namespace Tests\API\Reporting;

use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\AccountTypeGroup;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\CreditNoteStatus;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Interfaces\JobTagsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Locations\Models\Location;
use App\Components\Tags\Models\Tag;
use App\Http\Responses\Reporting\FinancialAccountsReceivablesReportResponse;
use App\Http\Responses\Reporting\FinancialRevenueReportResponse;
use App\Http\Responses\Reporting\FinancialVolumeReportResponse;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class ReportingFinancialControllerTest
 *
 * @package Tests\API\Reporting
 * @group   finance
 * @group   reporting
 */
class ReportingFinancialControllerTest extends ApiTestCase
{
    /**
     * @var array
     */
    private $filter;

    protected $permissions = [
        'finance.financial.reports.view',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $jobTagsService    = Container::getInstance()
            ->make(JobTagsServiceInterface::class);
        $accountingService = Container::getInstance()
            ->make(AccountingOrganizationsServiceInterface::class);

        $location            = factory(Location::class)->create();
        $accountOrganization = factory(AccountingOrganization::class)->create();
        $accountingService->addLocation($accountOrganization->id, $location->id);
        $glAccount = factory(GLAccount::class)->create([
            'account_type_id' => factory(AccountType::class)->create([
                'account_type_group_id' => factory(AccountTypeGroup::class)->create([
                    'name' => AccountTypeGroups::REVENUE,
                ])->id,
            ])->id,
        ]);
        $job       = factory(Job::class)->create([
            'assigned_location_id' => $location->id,
            'created_at'           => Carbon::now(),
        ]);
        /** @var \Illuminate\Support\Collection $tags */
        $tags = factory(Tag::class, $this->faker->numberBetween(2, 5))
            ->create()
            ->each(function (Tag $tag) use ($jobTagsService, $job) {
                $jobTagsService->assignTag($job->id, $tag->id);
            });
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
                'location_id' => $location->id,
                'job_id'      => $job->id,
                'date'        => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (Invoice $invoice) use ($tags, $glAccount) {
                factory(InvoiceItem::class, 3)->create([
                    'invoice_id'    => $invoice->id,
                    'gl_account_id' => $glAccount->id,
                ]);
                $invoice->tags()->attach($tags->random()->id);
                factory(InvoiceStatus::class)->create([
                    'invoice_id' => $invoice->id,
                    'status'     => FinancialEntityStatuses::APPROVED,
                ]);
                $amount  = $this->faker->randomFloat(2, 50, 100);
                $payment = factory(Payment::class)->create([
                    'type'   => PaymentTypes::DIRECT_DEPOSIT,
                    'amount' => $amount,
                ]);
                factory(InvoicePayment::class)->create([
                    'payment_id' => $payment->id,
                    'amount'     => $amount,
                    'invoice_id' => $invoice->id,
                    'is_fp'      => false,
                ]);
            });
        factory(CreditNote::class, $this->faker->numberBetween(2, 4))
            ->create([
                'location_id' => $location->id,
                'job_id'      => $job->id,
                'date'        => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (CreditNote $creditNote) use ($tags, $glAccount) {
                factory(CreditNoteItem::class, 3)->create([
                    'credit_note_id' => $creditNote->id,
                    'gl_account_id'  => $glAccount->id,
                ]);
                $creditNote->tags()->attach($tags->random()->id);
                factory(CreditNoteStatus::class)->create([
                    'credit_note_id' => $creditNote->id,
                    'status'         => FinancialEntityStatuses::APPROVED,
                ]);
            });
        factory(PurchaseOrder::class, $this->faker->numberBetween(2, 4))
            ->create([
                'location_id' => $location->id,
                'job_id'      => $job->id,
                'date'        => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (PurchaseOrder $purchaseOrder) use ($tags, $glAccount) {
                factory(PurchaseOrderItem::class, 3)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'gl_account_id'     => $glAccount->id,
                ]);
                $purchaseOrder->tags()->attach($tags->random()->id);
                factory(PurchaseOrderStatus::class)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'status'            => FinancialEntityStatuses::APPROVED,
                ]);
            });
        $prevMonth            = Carbon::now()->subMonth()->toDateString();
        $nextMonth            = Carbon::now()->addMonth()->toDateString();
        $this->filter         =[
            'location_id'        => $location->id,
            'current_date_from'  => $prevMonth,
            'current_date_to'    => $nextMonth,
            'previous_date_from' => $prevMonth,
            'previous_date_to'   => $nextMonth,
            'gl_account_id'      => $glAccount->id,
            'tag_ids'            => $tags->pluck('id')->toArray(),
        ];
    }

    public function testVolumeReport()
    {

        $url = action('Reporting\ReportingFinancialController@volumeReport', $this->filter);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(FinancialVolumeReportResponse::class, true)
            ->assertSeeData();
    }

    public function testVolumeReportReportShouldReturnValidationError()
    {
        $filter = [
            'location_id' => 0,
        ];

        $url = action('Reporting\ReportingFinancialController@volumeReport', $filter);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testRevenueReport()
    {
        $url = action('Reporting\ReportingFinancialController@revenueReport', $this->filter);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(FinancialRevenueReportResponse::class, true)
            ->assertSeeData();
    }

    public function testRevenueReportReportShouldReturnValidationError()
    {
        $filter = [
            'location_id' => 0,
        ];

        $url = action('Reporting\ReportingFinancialController@revenueReport', $filter);

        $this->getJson($url)
            ->assertStatus(422);
    }

    public function testAccountsReceivablesReport()
    {
        $url = action('Reporting\ReportingFinancialController@accountsReceivablesReport', $this->filter);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(FinancialAccountsReceivablesReportResponse::class, true)
            ->assertSeeData();
    }

    public function testAccountsReceivablesReportReportShouldReturnValidationError()
    {
        $filter = [
            'location_id' => 0,
        ];

        $url = action('Reporting\ReportingFinancialController@accountsReceivablesReport', $filter);

        $this->getJson($url)
            ->assertStatus(422);
    }
}
