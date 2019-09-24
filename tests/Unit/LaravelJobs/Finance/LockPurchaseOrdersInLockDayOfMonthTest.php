<?php

namespace Tests\Unit\LaravelJobs\Finance;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\PurchaseOrder;
use App\Jobs\Finance\LockFinancialEntity;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class LockPurchaseOrdersInLockDayOfMonthTest
 *
 * @package Tests\Unit\LaravelJobs\Finance
 */
class LockPurchaseOrdersInLockDayOfMonthTest extends TestCase
{
    public function testLockPurchaseOrdersWhichDateIsLessThanEndOfFinancialMonth()
    {
        $lockDay       = $this->faker->numberBetween(1, 15);
        $countOfOrders = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countOfOrders)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->subDay()
                ->toDateString(),
        ]);
        Carbon::setTestNow(Carbon::create(null, null, $lockDay));

        $notLockedPurchaseOrders = PurchaseOrder::query()
            ->whereNull('locked_at')
            ->get();
        self::assertCount($countOfOrders, $notLockedPurchaseOrders);

        (new LockFinancialEntity())->handle();

        $lockedPurchaseOrders = PurchaseOrder::query()
            ->whereNotNull('locked_at')
            ->get();
        self::assertCount($countOfOrders, $lockedPurchaseOrders);
    }

    public function testDoNotLockPurchaseOrdersWhichDateIsGreaterThanEndOfFinancialMonth()
    {
        $lockDay       = $this->faker->numberBetween(1, 15);
        $countOfOrders = $this->faker->numberBetween(1, 5);
        factory(PurchaseOrder::class, $countOfOrders)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
        ]);
        Carbon::setTestNow(Carbon::create(null, null, $lockDay));

        $notLockedPurchaseOrders = PurchaseOrder::query()
            ->whereNull('locked_at')
            ->get();
        self::assertCount($countOfOrders, $notLockedPurchaseOrders);

        (new LockFinancialEntity())->handle();

        $lockedPurchaseOrders = PurchaseOrder::query()
            ->whereNotNull('locked_at')
            ->get();
        self::assertCount(0, $lockedPurchaseOrders);
    }
}
