<?php

namespace App\Components\Finance;

use App\Components\Finance\DataProviders\CreditNoteCountersDataProvider;
use App\Components\Finance\DataProviders\CreditNoteListingDataProvider;
use App\Components\Finance\DataProviders\InvoiceCountersDataProvider;
use App\Components\Finance\DataProviders\InvoiceListingDataProvider;
use App\Components\Finance\DataProviders\PurchaseOrderCountersDataProvider;
use App\Components\Finance\DataProviders\PurchaseOrderListingDataProvider;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Interfaces\CreditNoteCountersDataProviderInterface;
use App\Components\Finance\Interfaces\CreditNoteListingDataProviderInterface;
use App\Components\Finance\Interfaces\CreditNotesListingServiceInterface;
use App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Interfaces\InvoiceCountersDataProviderInterface;
use App\Components\Finance\Interfaces\InvoiceListingDataProviderInterface;
use App\Components\Finance\Interfaces\InvoiceListingServiceInterface;
use App\Components\Finance\Interfaces\PaymentsServiceInterface;
use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use App\Components\Finance\Interfaces\PurchaseOrderItemsServiceInterface;
use App\Components\Finance\Interfaces\PurchaseOrderListingDataProviderInterface;
use App\Components\Finance\Interfaces\PurchaseOrderListingServiceInterface;
use App\Components\Finance\Interfaces\PurchaseOrderNotesServiceInterface;
use App\Components\Finance\Interfaces\PurchaseOrderTagsServiceInterface;
use App\Components\Finance\Interfaces\TransactionsServiceInterface;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Services\AccountingOrganizationsService;
use App\Components\Finance\Services\CreditNotesListingService;
use App\Components\Finance\Services\ForwardedPaymentsService;
use App\Components\Finance\Services\GLAccountService;
use App\Components\Finance\Services\InvoiceListingService;
use App\Components\Finance\Services\PaymentsService;
use App\Components\Finance\Services\PurchaseOrderItemsService;
use App\Components\Finance\Services\PurchaseOrderListingService;
use App\Components\Finance\Services\PurchaseOrderNotesService;
use App\Components\Finance\Services\PurchaseOrderTagsService;
use App\Components\Finance\Services\TransactionsService;
use App\Components\Omnipay\Pinpayment\Gateway as PinPayment;
use App\Observers\PositionableObserver;
use Illuminate\Support\ServiceProvider;
use Omnipay\Omnipay;

/**
 * Class FinanceServiceProvider
 *
 * @package App\Components\Finance
 */
class FinanceServiceProvider extends ServiceProvider
{
    public $bindings = [
        AccountingOrganizationsServiceInterface::class    => AccountingOrganizationsService::class,
        TransactionsServiceInterface::class               => TransactionsService::class,
        PaymentsServiceInterface::class                   => PaymentsService::class,
        GLAccountServiceInterface::class                  => GLAccountService::class,
        ForwardedPaymentsServiceInterface::class          => ForwardedPaymentsService::class,
        //Purchase Orders
        PurchaseOrderItemsServiceInterface::class         => PurchaseOrderItemsService::class,
        PurchaseOrderTagsServiceInterface::class          => PurchaseOrderTagsService::class,
        PurchaseOrderNotesServiceInterface::class         => PurchaseOrderNotesService::class,
        PurchaseOrderListingServiceInterface::class       => PurchaseOrderListingService::class,
        PurchaseOrderListingDataProviderInterface::class  => PurchaseOrderListingDataProvider::class,
        PurchaseOrderCountersDataProviderInterface::class => PurchaseOrderCountersDataProvider::class,
        //Invoices
        InvoiceListingServiceInterface::class             => InvoiceListingService::class,
        InvoiceListingDataProviderInterface::class        => InvoiceListingDataProvider::class,
        InvoiceCountersDataProviderInterface::class       => InvoiceCountersDataProvider::class,
        //Credit Notes
        CreditNoteListingDataProviderInterface::class     => CreditNoteListingDataProvider::class,
        CreditNotesListingServiceInterface::class         => CreditNotesListingService::class,
        CreditNoteCountersDataProviderInterface::class    => CreditNoteCountersDataProvider::class,
    ];

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CreditCardPaymentProcessor::class, function () {
            $gateway = Omnipay::create(
                config('omnipay.gateway') === 'pinpayment'
                    ? PinPayment::getClassName()
                    : config('omnipay.gateway')
            );

            return new CreditCardPaymentProcessor($gateway);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        InvoiceItem::observe(PositionableObserver::class);
        CreditNoteItem::observe(PositionableObserver::class);
        PurchaseOrderItem::observe(PositionableObserver::class);
    }
}
