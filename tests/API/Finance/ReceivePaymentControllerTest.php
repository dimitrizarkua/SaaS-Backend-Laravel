<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\Transaction;
use App\Components\Finance\Models\TransactionRecord;
use App\Components\Locations\Models\Location;
use App\Helpers\Decimal;
use Carbon\Carbon;
use Tests\API\ApiTestCase;
use Tests\Unit\Finance\GLAccountTestFactory;

/**
 * Class ReceivePaymentControllerTest
 *
 * @package Tests\API\Finance
 * @group   finance
 */
class ReceivePaymentControllerTest extends ApiTestCase
{
    public $permissions = ['finance.payments.transfers.receive'];

    /**
     * @var Location
     */
    private $location;

    /**
     * @var AccountingOrganization
     */
    private $accountingOrganization;

    public function setUp()
    {
        parent::setUp();
        $this->models = array_merge([
            TransactionRecord::class,
            Transaction::class,
            GLAccount::class,
            TaxRate::class,
            AccountType::class,
            AccountingOrganization::class,
            Payment::class,
            Location::class,
        ], $this->models);

        $this->location = factory(Location::class)->create();

        $this->accountingOrganization = factory(AccountingOrganization::class)->create(['is_active' => true]);

        $glAccountSrc = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = true,
            $balance = 0,
            $isBankAccount = true
        );

        $this->accountingOrganization->locations()->attach($this->location);

        $this->accountingOrganization->accounts_receivable_account_id = $glAccountSrc->id;
        $this->accountingOrganization->saveOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testSuccessPaymentReceive(): void
    {
        $glAccountDstDp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = 0,
            $isBankAccount = true
        );

        /* Default GLAccount for Invoices marked as FP */
        GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = 0,
            $isBankAccount = true,
            $code = GLAccount::FRANCHISE_PAYMENTS_ACCOUNT_CODE
        );

        $invoice = factory(Invoice::class)->create();
        $amount  = $this->faker->randomFloat(2, 10, 1000);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = [
            'payment_data'  => [
                'paid_at'           => Carbon::now()->format('Y-m-d\TH:i:s\Z'),
                'reference'         => 'test',
                'location_id'       => $this->location->id,
                'dst_gl_account_id' => $glAccountDstDp->id,
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'is_fp'      => false,
                    'amount'     => $amount,
                ],
            ],
        ];

        $url = action('Finance\ReceivePaymentController@receive', $data);

        $this->postJson($url, $data)
            ->assertStatus(200);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToReceivePaymentWhenDestinationGLAccountForDirectDepositIsNotBankAccount(): void
    {
        $glAccountDstDp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = 0,
            $isBankAccount = false
        );

        /* Default GLAccount for Invoices marked as FP */
        GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = 0,
            $isBankAccount = true,
            $code = GLAccount::FRANCHISE_PAYMENTS_ACCOUNT_CODE
        );

        $invoice = factory(Invoice::class)->create();
        $amount  = $this->faker->randomFloat(2, 10, 1000);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = [
            'payment_data'  => [
                'paid_at'           => Carbon::now()->format('Y-m-d\TH:i:s\Z'),
                'reference'         => 'test',
                'location_id'       => $this->location->id,
                'dst_gl_account_id' => $glAccountDstDp->id,
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'is_fp'      => false,
                    'amount'     => $amount,
                ],
            ],
        ];

        $url = action('Finance\ReceivePaymentController@receive', $data);

        $this->postJson($url, $data)
            ->assertNotAllowed('Debit account must be bank account.');
    }

    /**
     * @throws \Throwable
     */
    public function testFailToReceivePaymentWhenDestinationGLAccountForForwardedPaymentIsNotExist(): void
    {
        $glAccountDstDp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = 0,
            $isBankAccount = false
        );

        $invoice = factory(Invoice::class)->create();
        $amount  = $this->faker->randomFloat(2, 10, 1000);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = [
            'payment_data'  => [
                'paid_at'           => Carbon::now()->format('Y-m-d\TH:i:s\Z'),
                'reference'         => 'test',
                'location_id'       => $this->location->id,
                'dst_gl_account_id' => $glAccountDstDp->id,
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'is_fp'      => true,
                    'amount'     => $amount,
                ],
            ],
        ];

        $url = action('Finance\ReceivePaymentController@receive', $data);

        $this->postJson($url, $data)
            ->assertNotAllowed('Franchise Payments (Holding) account does not exist.');
    }

    /**
     * @throws \Throwable
     */
    public function testFailToPaymentRecieveWhenInvoicePaymentIsMoreThanBalanceDue(): void
    {
        $glAccountDstDp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = 0,
            $isBankAccount = true
        );

        $invoice = factory(Invoice::class)->create();
        $amount  = $this->faker->randomFloat(2, 10, 1000);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $data = [
            'payment_data'  => [
                'paid_at'           => Carbon::now()->format('Y-m-d\TH:i:s\Z'),
                'reference'         => 'test',
                'location_id'       => $this->location->id,
                'dst_gl_account_id' => $glAccountDstDp->id,
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'is_fp'      => false,
                    'amount'     => $invoice->getAmountDue() + 10,
                ],
            ],
        ];

        $url = action('Finance\ReceivePaymentController@receive', $data);

        $this->postJson($url, $data)
            ->assertNotAllowed('Invoice payment value cannot be more balance due value.');
    }

    /**
     * @throws \Throwable
     */
    public function testFailToPaymentRecieveWhenInvoicePaymentIsLessThanBalanceDue(): void
    {
        $glAccountDstDp = GLAccountTestFactory::createGLAccountWithBalance(
            $this->accountingOrganization->id,
            $increaseActionIsDebit = false,
            $balance = 10000,
            $isBankAccount = true
        );

        $invoice = factory(Invoice::class)->create();
        $amount  = $this->faker->randomFloat(2, 20, 1000);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'unit_cost'  => $amount,
            'quantity'   => 1,
            'discount'   => 0,
        ]);

        $invoiceBalanceDueBeforeReceive = $invoice->getAmountDue();

        $receiveAmount = $this->faker->randomFloat(2, $amount - 10, $amount - 1);

        $data = [
            'payment_data'  => [
                'paid_at'           => Carbon::now()->format('Y-m-d\TH:i:s\Z'),
                'reference'         => 'test',
                'location_id'       => $this->location->id,
                'dst_gl_account_id' => $glAccountDstDp->id,
            ],
            'invoices_list' => [
                [
                    'invoice_id' => $invoice->id,
                    'is_fp'      => false,
                    'amount'     => $receiveAmount,
                ],
            ],
        ];

        $url = action('Finance\ReceivePaymentController@receive', $data);

        $this->postJson($url, $data);

        $invoice->refresh();
        $invoiceBalanceDueAfterReceive = $invoice->getAmountDue();

        self::assertTrue(Decimal::areEquals(
            $invoiceBalanceDueBeforeReceive - $invoiceBalanceDueAfterReceive,
            $receiveAmount
        ));
    }
}
