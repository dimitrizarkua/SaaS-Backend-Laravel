<?php

namespace Tests\Unit\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\InvoicePaymentType;
use App\Components\Finance\Enums\InvoiceVirtualStatuses;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\Payment;
use App\Components\Locations\Models\Location;
use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class InvoicesTestFactory
 *
 * @package Tests\Unit\Finance
 */
class InvoicesTestFactory
{
    /**
     * @param int    $count
     * @param array  $data
     * @param string $status
     * @param bool   $withApproveRequest
     *
     * @return \Illuminate\Support\Collection
     */
    public static function createInvoices(
        $count = 1,
        array $data = [],
        string $status = FinancialEntityStatuses::DRAFT,
        bool $withApproveRequest = false
    ): Collection {
        /** @var Collection $collection */
        $collection = factory(Invoice::class, $count)
            ->create($data);
        if ($status === FinancialEntityStatuses::APPROVED) {
            $collection->each(function (Invoice $invoice) use ($status) {
                factory(InvoiceStatus::class)->create([
                    'invoice_id' => $invoice->id,
                    'status'     => $status,
                    'created_at' => Carbon::now()->addSeconds(10),
                ]);
            });
        }

        if (true === $withApproveRequest) {
            $collection->each(function (Invoice $invoice) {
                factory(InvoiceApproveRequest::class)->create([
                    'invoice_id'  => $invoice->id,
                    'approved_at' => null,
                ]);
            });
        }

        return $collection;
    }

    /**
     * @param array $data
     *
     * @return \App\Components\Finance\Models\Invoice
     */
    public static function createDraftInvoice(array $data = []): Invoice
    {
        $invoice = \factory(Invoice::class)->create($data);
        \factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);

        return $invoice;
    }

    /**
     * @param \App\Models\User                               $user
     * @param int                                            $countOfDraft
     * @param int                                            $countOfUnpaid
     * @param int                                            $countOfOverdue
     * @param \App\Components\Locations\Models\Location|null $location
     * @param int                                            $countOfUnforwarded
     *
     * @return array $invoices
     */
    public static function createListOfInvoices(
        User $user,
        int $countOfDraft = 0,
        int $countOfUnpaid = 0,
        int $countOfOverdue = 0,
        Location $location = null,
        int $countOfUnforwarded = 0
    ) {
        $faker = Factory::create();

        if (null === $location) {
            $location = factory(Location::class)
                ->create();
        }
        $user->locations()->attach($location);

        /**
         * Draft
         */
        $invoices[FinancialEntityStatuses::DRAFT] = InvoicesTestFactory::createInvoices($countOfDraft, [
            'location_id' => $location->id,
        ]);
        /**
         * Unpaid
         */
        $invoices[InvoiceVirtualStatuses::UNPAID] = InvoicesTestFactory::createInvoices($countOfUnpaid, [
            'location_id' => $location->id,
        ], FinancialEntityStatuses::APPROVED)->each(function (Invoice $invoice) use ($faker) {
            factory(InvoiceItem::class, $faker->numberBetween(1, 3))->create([
                'invoice_id' => $invoice->id,
            ]);
        });
        /**
         * Overdue
         */
        $invoices[InvoiceVirtualStatuses::OVERDUE] = InvoicesTestFactory::createInvoices(
            $countOfOverdue,
            [
                'location_id' => $location->id,
                'due_at'      => Carbon::yesterday(),
            ],
            FinancialEntityStatuses::APPROVED
        )->each(function (Invoice $invoice) {
            /** @var Payment $payment */
            $payment = factory(Payment::class)->create();
            $invoice->payments()->attach($payment, [
                'amount' => $payment->amount,
            ]);

            factory(InvoiceItem::class)->create([
                'invoice_id' => $invoice->id,
                'unit_cost'  => $payment->amount * 3,
                'quantity'   => 1,
                'discount'   => 0,
            ]);
        });

        /**
         * Unforwarded
         */
        $invoices[InvoicePaymentType::FORWARDED] = InvoicesTestFactory::createInvoices(
            $countOfUnforwarded,
            [
                'location_id' => $location->id,
            ],
            FinancialEntityStatuses::APPROVED
        )->each(function (Invoice $invoice) {
            /** @var Payment $payment */
            $payment = factory(Payment::class)->create();
            $invoice->payments()->attach($payment, [
                'amount' => $payment->amount,
                'is_fp'  => true,
            ]);

            factory(InvoiceItem::class)->create([
                'invoice_id' => $invoice->id,
                'unit_cost'  => $payment->amount * 3,
                'quantity'   => 1,
                'discount'   => 0,
            ]);
        });

        return $invoices;
    }

    /**
     * @param array $invoiceIds
     * @param null  $type
     * @param int   $minAmount
     * @param int   $maxAmount
     * @param null  $isFp
     */
    public static function createPaymentsForInvoices(
        array $invoiceIds,
        $type = null,
        $minAmount = 10,
        $maxAmount = 100,
        $isFp = null
    ) {
        $faker = Factory::create();

        foreach ($invoiceIds as $invoiceId) {
            /** @var Payment $payment */
            $payment = factory(Payment::class)->create([
                'type' => $type ?? $faker->randomElement(PaymentTypes::values()),
            ]);

            InvoicePayment::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoiceId,
                'amount'     => $faker->randomFloat(2, $minAmount, $maxAmount),
                'is_fp'      => $isFp ?? $faker->boolean,
            ]);
        }
    }
}
