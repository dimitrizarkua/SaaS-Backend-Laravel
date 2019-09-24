<?php

namespace Tests\Unit\Reporting\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportStatus;
use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
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
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Finance\Models\VO\GLAccountTransactionFilter;
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
use App\Components\Reporting\Models\Filters\FinancialReportFilterData;
use App\Components\Reporting\Services\FinancialAccountsReceivablesReportService;
use App\Components\Reporting\Services\FinancialRevenueReportService;
use App\Components\Reporting\Services\FinancialVolumeReportService;
use App\Components\Tags\Models\Tag;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class ReportingFinancialServiceTest
 *
 * @package Tests\Unit\Reporting\Services
 * @group   finance
 * @group   reporting
 */
class ReportingFinancialServiceTest extends TestCase
{
    /**
     * @var \App\Components\Reporting\Interfaces\FinancialReportServiceInterface
     */
    private $financialVolumeReportService;
    /**
     * @var GLAccountServiceInterface $glAccountService
     */
    private $glAccountService;
    /**
     * @var \App\Components\Reporting\Interfaces\FinancialReportServiceInterface
     */
    private $financialRevenueReportService;
    /**
     * @var \App\Components\Reporting\Interfaces\FinancialReportServiceInterface
     */
    private $financialAccountsReceivableReportService;
    /**
     * @var \App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface
     */
    private $accountingService;
    /**
     * @var \App\Components\Jobs\Interfaces\JobTagsServiceInterface
     */
    private $jobTagsService;
    /**
     * @var FinancialReportFilterData
     */
    private $filter;
    /**
     * @var Job
     */
    private $job;
    /**
     * @var Location
     */
    private $location;
    /**
     * @var GLAccount
     */
    private $glAccount;
    /**
     * @var AccountingOrganization
     */
    private $accountOrganization;
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $purchaseOrders;
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $creditNotes;
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $invoices;
    /**
     * @var JobLahaCompensation
     */
    private $jobLahaCompensation;
    /**
     * @var JobReimbursement
     */
    private $jobReimbursement;
    /**
     * @var JobEquipment
     */
    private $jobEquipment;
    /**
     * @var JobMaterial
     */
    private $jobMaterial;
    /**
     * @var JobLabour
     */
    private $jobLabour;

    /**
     * @throws \JsonMapper_Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->glAccountService                         = Container::getInstance()
            ->make(GLAccountServiceInterface::class);
        $this->financialVolumeReportService             = Container::getInstance()
            ->make(FinancialVolumeReportService::class);
        $this->financialRevenueReportService            = Container::getInstance()
            ->make(FinancialRevenueReportService::class);
        $this->financialAccountsReceivableReportService = Container::getInstance()
            ->make(FinancialAccountsReceivablesReportService::class);
        $this->jobTagsService                           = Container::getInstance()
            ->make(JobTagsServiceInterface::class);
        $this->accountingService                        = Container::getInstance()
            ->make(AccountingOrganizationsServiceInterface::class);


        $this->location            = factory(Location::class)->create();
        $this->accountOrganization = factory(AccountingOrganization::class)->create();
        $this->accountingService->addLocation($this->accountOrganization->id, $this->location->id);
        $this->glAccount = factory(GLAccount::class)->create([
            'account_type_id' => factory(AccountType::class)->create([
                'account_type_group_id' => factory(AccountTypeGroup::class)->create([
                    'name' => AccountTypeGroups::REVENUE,
                ])->id,
            ])->id,
        ]);
        $this->job       = factory(Job::class)->create([
            'assigned_location_id' => $this->location->id,
            'created_at'           => Carbon::now(),
        ]);
        /** @var \Illuminate\Support\Collection $tags */
        $tags = factory(Tag::class, $this->faker->numberBetween(2, 5))
            ->create()
            ->each(function (Tag $tag) {
                $this->jobTagsService->assignTag($this->job->id, $tag->id);
            });
        /** @var JobLabour $jobLabour */
        $this->jobLabour = factory(JobLabour::class)->create([
            'job_id' => $this->job->id,
        ]);
        /** @var JobMaterial $jobMaterial */
        $this->jobMaterial = factory(JobMaterial::class)->create([
            'job_id' => $this->job->id,
        ]);
        /** @var JobEquipment $jobEquipment */
        $this->jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $this->job->id,
        ]);
        /** @var JobReimbursement $jobReimbursement */
        $this->jobReimbursement = factory(JobReimbursement::class)->create([
            'job_id'        => $this->job->id,
            'is_chargeable' => true,
        ]);
        /** @var JobLahaCompensation $jobLahaCompensation */
        $this->jobLahaCompensation = factory(JobLahaCompensation::class)->create([
            'job_id' => $this->job->id,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $this->job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $this->invoices       = factory(Invoice::class, $this->faker->numberBetween(2, 4))
            ->create([
                'location_id' => $this->location->id,
                'job_id'      => $this->job->id,
                'date'        => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (Invoice $invoice) use ($tags) {
                factory(InvoiceItem::class, 3)->create([
                    'invoice_id'    => $invoice->id,
                    'gl_account_id' => $this->glAccount->id,
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
        $this->creditNotes    = factory(CreditNote::class, $this->faker->numberBetween(2, 4))
            ->create([
                'location_id' => $this->location->id,
                'job_id'      => $this->job->id,
                'date'        => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (CreditNote $creditNote) use ($tags) {
                factory(CreditNoteItem::class, 3)->create([
                    'credit_note_id' => $creditNote->id,
                    'gl_account_id'  => $this->glAccount->id,
                ]);
                $creditNote->tags()->attach($tags->random()->id);
                factory(CreditNoteStatus::class)->create([
                    'credit_note_id' => $creditNote->id,
                    'status'         => FinancialEntityStatuses::APPROVED,
                ]);
            });
        $this->purchaseOrders = factory(PurchaseOrder::class, $this->faker->numberBetween(2, 4))
            ->create([
                'location_id' => $this->location->id,
                'job_id'      => $this->job->id,
                'date'        => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (PurchaseOrder $purchaseOrder) use ($tags) {
                factory(PurchaseOrderItem::class, 3)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'gl_account_id'     => $this->glAccount->id,
                ]);
                $purchaseOrder->tags()->attach($tags->random()->id);
                factory(PurchaseOrderStatus::class)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'status'            => FinancialEntityStatuses::APPROVED,
                ]);
            });
        $prevMonth            = Carbon::now()->subMonth();
        $nextMonth            = Carbon::now()->addMonth();
        $this->filter         = new FinancialReportFilterData([
            'location_id'        => $this->location->id,
            'current_date_from'  => $prevMonth,
            'current_date_to'    => $nextMonth,
            'previous_date_from' => $prevMonth,
            'previous_date_to'   => $nextMonth,
            'gl_account_id'      => $this->glAccount->id,
            'tag_ids'            => $tags->pluck('id')->toArray(),
        ]);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testGetFinancialVolumeReport(): void
    {
        $result = $this->financialVolumeReportService->getReport($this->filter);

        $totalRevenue         = InvoicePayment::query()
            ->whereIn('invoice_id', $this->invoices->pluck('id'))
            ->sum('amount');
        $invoicesAmount       = InvoiceItem::query()
            ->leftJoin('tax_rates', 'invoice_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('invoice_id', $this->invoices->pluck('id'))
            ->sum(DB::raw('(unit_cost * (1 - (discount / 100))) * (1 + tax_rates.rate) * quantity'));
        $creditNotesAmount    = CreditNoteItem::query()
            ->leftJoin('tax_rates', 'credit_note_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('credit_note_id', $this->creditNotes->pluck('id'))
            ->sum(DB::raw('unit_cost * (1 + tax_rates.rate) * quantity'));
        $purchaseOrdersAmount = PurchaseOrderItem::query()
            ->leftJoin('tax_rates', 'purchase_order_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('purchase_order_id', $this->purchaseOrders->pluck('id'))
            ->sum(DB::raw('(unit_cost * (1 + (markup / 100))) * (1 + tax_rates.rate) * quantity'));
        $totalCharged         = $invoicesAmount - $creditNotesAmount;
        $totalCosts           = $this->jobLabour->calculateTotalAmount()
            + $this->jobMaterial->buy_cost_per_unit * $this->jobMaterial->quantity_used_override
            + $this->jobEquipment->buy_cost_per_interval * $this->jobEquipment->intervals_count_override
            + $purchaseOrdersAmount
            + $this->jobReimbursement->total_amount
            + $this->jobLahaCompensation->rate_per_day * $this->jobLahaCompensation->days;

        self::assertEquals($this->invoices->count(), $result['invoices']);
        self::assertEquals($this->creditNotes->count(), $result['credit_notes']);
        self::assertEquals($this->purchaseOrders->count(), $result['purchase_orders']);
        self::assertEquals($totalRevenue, $result['total_revenue']);
        self::assertEquals(
            $totalCharged
                ? ($totalCharged - $totalCosts) / $totalCharged * 100
                : 0,
            $result['total_gross_profit']
        );
        self::assertEquals($invoicesAmount, $result['accounts_receivable']);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetFinancialVolumeReportWithNoEntities(): void
    {
        $location  = factory(Location::class)->create();
        $prevMonth = Carbon::now()->subMonth();
        $nextMonth = Carbon::now()->addMonth();
        $filter    = new FinancialReportFilterData([
            'location_id'        => $location->id,
            'current_date_from'  => $prevMonth,
            'current_date_to'    => $nextMonth,
            'previous_date_from' => $prevMonth,
            'previous_date_to'   => $nextMonth,
        ]);
        $result    = $this->financialVolumeReportService->getReport($filter);

        self::assertEquals(0, $result['invoices']);
        self::assertEquals(0, $result['credit_notes']);
        self::assertEquals(0, $result['purchase_orders']);
        self::assertEquals(0, $result['total_revenue']);
        self::assertEquals(0, $result['total_gross_profit']);
        self::assertEquals(0, $result['accounts_receivable']);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testGetFinancialRevenueReport(): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection $assessmentReports */
        $assessmentReports = factory(AssessmentReport::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $this->job->id,
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

        $accountTypeGroup = AccountTypeGroup::query()->where('name', AccountTypeGroups::REVENUE)->first();
        $accountTypeGroup = $accountTypeGroup ?? factory(AccountTypeGroup::class)->create([
                'name' => AccountTypeGroups::REVENUE,
            ]);
        $accountType      = factory(AccountType::class)->create([
            'increase_action_is_debit' => true,
            'account_type_group_id'    => $accountTypeGroup->id,
        ]);
        /** @var GLAccount $glAccount */
        $glAccount   = factory(GLAccount::class)
            ->create([
                'is_active'                  => true,
                'account_type_id'            => $accountType->id,
                'accounting_organization_id' => $this->accountOrganization->id,
            ]);
        $transaction = factory(Transaction::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'created_at'                 => new Carbon(),
        ]);
        factory(TransactionRecord::class, $this->faker->numberBetween(1, 5))
            ->create([
                'gl_account_id'  => $glAccount->id,
                'transaction_id' => $transaction->id,
            ]);
        $glAccountFilter       = new GLAccountTransactionFilter([
            'date_from' => $this->filter->getCurrentDateFrom(),
            'date_to'   => $this->filter->getCurrentDateTo(),
        ]);
        $preparedAccountList[] =
            [
                'name'   => $glAccount->name,
                'code'   => $glAccount->code,
                'amount' => $this->glAccountService->getAccountBalance($glAccount->id, $glAccountFilter),
            ];
        /** @var Invoice $taggedInvoice */
        $taggedInvoice = $this->invoices->first();
        $taggedInvoice->tags()->attach(factory(Tag::class)->create()->id);

        $result = $this->financialRevenueReportService->getReport($this->filter);

        $invoicesPaid            = InvoicePayment::query()
            ->whereIn('invoice_id', $this->invoices->pluck('id'))
            ->sum('amount');
        $invoicesAmount          = InvoiceItem::query()
            ->leftJoin('tax_rates', 'invoice_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('invoice_id', $this->invoices->pluck('id'))
            ->sum(DB::raw('(unit_cost * (1 - (discount / 100))) * (1 + tax_rates.rate) * quantity'));
        $creditNotesAmount       = CreditNoteItem::query()
            ->leftJoin('tax_rates', 'credit_note_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('credit_note_id', $this->creditNotes->pluck('id'))
            ->sum(DB::raw('unit_cost * (1 + tax_rates.rate) * quantity'));
        $purchaseOrdersAmount    = PurchaseOrderItem::query()
            ->leftJoin('tax_rates', 'purchase_order_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('purchase_order_id', $this->purchaseOrders->pluck('id'))
            ->sum(DB::raw('(unit_cost * (1 + (markup / 100))) * (1 + tax_rates.rate) * quantity'));
        $assessmentReportsAmount = $assessmentReports->reduce(
            function (float $carry, AssessmentReport $assessmentReport) {
                return $carry + $assessmentReport->getTotalAmount() + $assessmentReport->getTax();
            },
            0
        );
        $totalCharged            = $invoicesAmount - $creditNotesAmount;
        $totalCosts              = $this->jobLabour->calculateTotalAmount()
            + $this->jobMaterial->buy_cost_per_unit * $this->jobMaterial->quantity_used_override
            + $this->jobEquipment->buy_cost_per_interval * $this->jobEquipment->intervals_count_override
            + $purchaseOrdersAmount
            + $this->jobReimbursement->total_amount
            + $this->jobLahaCompensation->rate_per_day * $this->jobLahaCompensation->days;
        $this->filter->updateJobIds();
        $jobCount = count($this->filter->getCurrentPeriodJobIds());

        self::assertEquals($invoicesPaid, $result['invoices_paid']);
        self::assertEquals($invoicesAmount, $result['invoices_written']);
        self::assertEquals($jobCount > 0 ? $totalCosts / $jobCount : 0, $result['avg_job_cost']);
        self::assertEquals(
            $jobCount > 0 ? $assessmentReportsAmount / $jobCount : 0,
            $result['avg_over_job_cost']
        );
        $totalCharged = $totalCharged ? ($totalCharged - $totalCosts) / $totalCharged * 100 : 0;
        self::assertEquals(round($totalCharged, 2), round($result['total_gross_profit'], 2));
        self::assertEquals($preparedAccountList, $result['revenue_accounts']);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetFinancialRevenueReportWithNoEntities(): void
    {
        $location            = factory(Location::class)->create();
        $accountOrganization = factory(AccountingOrganization::class)->create();
        $this->accountingService->addLocation($accountOrganization->id, $location->id);
        $prevMonth = Carbon::now()->subMonth();
        $nextMonth = Carbon::now()->addMonth();
        $filter    = new FinancialReportFilterData([
            'location_id'        => $location->id,
            'current_date_from'  => $prevMonth,
            'current_date_to'    => $nextMonth,
            'previous_date_from' => $prevMonth,
            'previous_date_to'   => $nextMonth,
        ]);
        $result    = $this->financialRevenueReportService->getReport($filter);

        self::assertEquals(0, $result['invoices_paid']);
        self::assertEquals(0, $result['invoices_written']);
        self::assertEquals(0, $result['avg_job_cost']);
        self::assertEquals(0, $result['avg_over_job_cost']);
        self::assertEquals(0, $result['credit_notes']);
        self::assertEquals(0, $result['total_gross_profit']);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testGetFinancialAccountsReceivablesReport(): void
    {
        /** @var Invoice $taggedInvoice */
        $taggedInvoice = $this->invoices->first();
        $taggedInvoice->tags()->attach(factory(Tag::class)->create()->id);

        $date = clone $this->filter->getCurrentDateFrom();
        /** @var \Illuminate\Support\Collection $invoicesForCurrentPeriod */
        $invoicesForCurrentPeriod = $this->createInvoicesForDate($date->subDays($this->faker->numberBetween(1, 29)));
        /** @var \Illuminate\Support\Collection $invoicesFor30Period */
        $invoicesFor30Period = $this->createInvoicesForDate($date->subDays(30));
        /** @var \Illuminate\Support\Collection $invoicesFor60Period */
        $invoicesFor60Period = $this->createInvoicesForDate($date->subDays(30));
        /** @var \Illuminate\Support\Collection $invoicesFor90Period */
        $invoicesFor90Period = $this->createInvoicesForDate($date->subDays($this->faker->numberBetween(30, 100)));

        $result = $this->financialAccountsReceivableReportService->getReport($this->filter);

        $invoicesForCurrentPeriodReceivables = $invoicesForCurrentPeriod->reduce(
            function (float $total, Invoice $invoice) {
                return $total + $invoice->getTotalAmount() - $invoice->getTotalPaid();
            },
            0
        );
        $invoicesFor30PeriodReceivables      = $invoicesFor30Period->reduce(
            function (float $total, Invoice $invoice) {
                return $total + $invoice->getTotalAmount() - $invoice->getTotalPaid();
            },
            0
        );
        $invoicesFor60PeriodReceivables      = $invoicesFor60Period->reduce(
            function (float $total, Invoice $invoice) {
                return $total + $invoice->getTotalAmount() - $invoice->getTotalPaid();
            },
            0
        );
        $invoicesFor90PeriodReceivables      = $invoicesFor90Period->reduce(
            function (float $total, Invoice $invoice) {
                return $total + $invoice->getTotalAmount() - $invoice->getTotalPaid();
            },
            0
        );

        self::assertEquals($invoicesForCurrentPeriodReceivables, $result['current']);
        self::assertEquals($invoicesFor30PeriodReceivables, $result['more_30_days']);
        self::assertEquals($invoicesFor60PeriodReceivables, $result['more_60_days']);
        self::assertEquals($invoicesFor90PeriodReceivables, $result['more_90_days']);
        self::assertEquals(
            $invoicesForCurrentPeriodReceivables
            + $invoicesFor30PeriodReceivables
            + $invoicesFor60PeriodReceivables
            + $invoicesFor90PeriodReceivables,
            $result['total']
        );
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testGetFinancialAccountsReceivablesReportWithNoEntities(): void
    {
        $location  = factory(Location::class)->create();
        $prevMonth = Carbon::now()->subMonth();
        $nextMonth = Carbon::now()->addMonth();
        $filter    = new FinancialReportFilterData([
            'location_id'        => $location->id,
            'current_date_from'  => $prevMonth,
            'current_date_to'    => $nextMonth,
            'previous_date_from' => $prevMonth,
            'previous_date_to'   => $nextMonth,
        ]);
        $result    = $this->financialAccountsReceivableReportService->getReport($filter);

        self::assertEquals(0, $result['current']);
        self::assertEquals(0, $result['more_30_days']);
        self::assertEquals(0, $result['more_60_days']);
        self::assertEquals(0, $result['more_90_days']);
        self::assertEquals(0, $result['total']);
    }

    /**
     * @param $date
     *
     * @return \Illuminate\Support\Collection
     */
    private function createInvoicesForDate(Carbon $date): Collection
    {
        return factory(Invoice::class, $this->faker->numberBetween(1, 1))
            ->create([
                'location_id' => $this->location->id,
                'job_id'      => $this->job->id,
                'due_at'      => $date,
            ])
            ->each(function (Invoice $invoice) {
                factory(InvoiceItem::class, 3)->create([
                    'invoice_id'    => $invoice->id,
                    'gl_account_id' => $this->glAccount->id,
                ]);
                $invoice->tags()->attach($this->faker->randomElement($this->filter->tag_ids));

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
    }
}
