<?php

namespace Tests\Unit\Finance\Models;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use Tests\TestCase;

/**
 * Class InvoiceModelTest
 *
 * @package Tests\Unit\Finance\Models
 * @group   finance
 * @group   invoices
 */
class InvoiceModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->models = array_merge([
            InvoiceItem::class,
            InvoiceApproveRequest::class,
            InvoiceStatus::class,
            Invoice::class,
        ], $this->models);
    }

    public function testInvoiceShouldBeDraft(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        self::assertTrue($invoice->isDraft());
    }

    public function testInvoiceShouldNotBeDraft(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => 'SOME_NON_DRAFT_STATUS',
        ]);

        self::assertFalse($invoice->isDraft());
    }

    public function testInvoiceShouldNotHasApprovalRequests(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();

        self::assertFalse($invoice->hasApproveRequests());
    }

    public function testInvoiceShouldHasApprovalRequests(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id' => $invoice->id,
        ]);

        self::assertTrue($invoice->hasApproveRequests());
    }

    public function testInvoiceCanNotBeDeleted(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id' => $invoice->id,
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => 'SOME_NON_DRAFT_STATUS',
        ]);

        self::assertFalse($invoice->canBeDeleted());
    }

    public function testInvoiceCanNotBeDeletedWithNonDraftStatus(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => 'SOME_NON_DRAFT_STATUS',
        ]);

        self::assertFalse($invoice->canBeDeleted());
    }

    public function testInvoiceCanNotBeDeletedWithApproveRequest(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceApproveRequest::class)->create([
            'invoice_id' => $invoice->id,
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        self::assertFalse($invoice->canBeDeleted());
    }

    public function testInvoiceCanBeDeleted(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        self::assertTrue($invoice->canBeDeleted());
    }

    public function testShouldReturnCorrectPaidValue(): void
    {
        /** @var Invoice $invoice */
        $invoice         = factory(Invoice::class)->create();
        $countOfPayments = $this->faker->numberBetween(2, 4);
        $totalPaid       = 0;
        factory(Payment::class, $countOfPayments)
            ->create()
            ->each(function (Payment $payment) use ($invoice, &$totalPaid) {
                $totalPaid += $payment->amount;
                $invoice
                    ->payments()
                    ->attach($payment, [
                        'amount' => $payment->amount,
                    ]);
            });

        self::assertEquals($totalPaid, $invoice->getTotalPaid());
    }

    public function testShouldReturnCorrectValueOfTotalAmount(): void
    {
        /** @var Invoice $invoice */
        $invoice      = factory(Invoice::class)->create();
        $countOfItems = $this->faker->numberBetween(2, 4);
        $totalAmount  = 0;
        factory(InvoiceItem::class, $countOfItems)
            ->create([
                'invoice_id' => $invoice->id,
            ])
            ->each(function (InvoiceItem $item) use (&$totalAmount) {
                $totalAmount += $item->getTotalAmount();
            });

        self::assertEquals($totalAmount, $invoice->getTotalAmount());
    }
}
