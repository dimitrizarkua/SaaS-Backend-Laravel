<?php

namespace Tests\Unit\Finance\Models;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\InvoiceStatus;
use Tests\TestCase;

/**
 * Class InvoiceStatusModelTest
 *
 * @package Tests\Unit\Finance\Models
 *
 * @group   invoices
 * @group   finance
 */
class InvoiceStatusModelTest extends TestCase
{
    public function testCanChangeShouldBeTrue()
    {
        $invoiceStatus = new InvoiceStatus(['status' => FinancialEntityStatuses::DRAFT]);
        self::assertTrue($invoiceStatus->canBeChangedTo(FinancialEntityStatuses::APPROVED));
    }

    public function testCanChangeShouldBeFalseWhenChangingToTheSameStatus()
    {
        $invoiceStatus = new InvoiceStatus(['status' => FinancialEntityStatuses::DRAFT]);
        self::assertFalse($invoiceStatus->canBeChangedTo(FinancialEntityStatuses::DRAFT));
    }

    public function testCanChangeShoudlBeFalseWhenChangingToNoneExistingStatus()
    {
        $invoiceStatus = new InvoiceStatus(['status' => FinancialEntityStatuses::DRAFT]);
        self::assertFalse($invoiceStatus->canBeChangedTo('non_existing_status'));
    }
}
