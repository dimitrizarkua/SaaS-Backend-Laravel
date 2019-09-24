<?php

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Suburb;
use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Domains\FinancialTransaction;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\TaxRates;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Interfaces\InvoiceCountersDataProviderInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Jobs\Finance\CreateDefaultGLAccounts;
use App\Jobs\Finance\RecalculateCounters;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class InvoicesSeeder
 */
class InvoicesSeeder extends Seeder
{
    /**
     * @var \App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface
     */
    private $accountingOrganizationService;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * InvoicesSeeder constructor.
     */
    public function __construct(AccountingOrganizationsServiceInterface $accountingOrganizationService)
    {
        if (!app()->environment(['local'])) {
            throw new RuntimeException('This seeder can be ran only on local env');
        }
        $this->accountingOrganizationService = $accountingOrganizationService;
        $this->faker                         = \Faker\Factory::create();
    }

    /**
     * @throws \Throwable
     */
    public function run()
    {
        $locationList = Location::query()->get();

        try {
            $this->createAccountTypes();
            Invoice::disableSearchSyncing();
            Contact::disableSearchSyncing();
            DB::transaction(function () use ($locationList) {
                foreach ($locationList as $location) {
                    $this->createDraftInvoices($location);
                    $this->createUnpaidInvoices($location);
                    $this->createOverDueInvoices($location);
                    $this->createPaid($location);
                }
            });
        } finally {
            Invoice::enableSearchSyncing();
            Contact::enableSearchSyncing();
        }

        $invoiceDataProvider = app()->make(InvoiceCountersDataProviderInterface::class);
        $locationIds         = $locationList->pluck('id')->toArray();

        RecalculateCounters::dispatch($invoiceDataProvider, $locationIds);
        Artisan::call('scout:import', ['model' => Invoice::class]);
        Artisan::call('scout:import', ['model' => Contact::class]);
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @throws \Throwable
     */
    private function createDraftInvoices(Location $location): void
    {
        $this->createListInvoices(function ($userId) use ($location) {
            $invoice = $this->createInvoice($location);
            factory(InvoiceStatus::class)->create([
                'invoice_id' => $invoice->id,
                'user_id'    => $userId,
                'status'     => FinancialEntityStatuses::DRAFT,
            ]);

            return $invoice;
        });
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @throws \Throwable
     */
    private function createUnpaidInvoices(Location $location): void
    {
        $this->createListInvoices(function ($userId) use ($location) {
            $invoice = $this->createInvoice($location);
            factory(InvoiceStatus::class)->create([
                'invoice_id' => $invoice->id,
                'user_id'    => $userId,
                'status'     => FinancialEntityStatuses::APPROVED,
            ]);

            $accountingOrganization = $invoice->accountingOrganization;
            $transaction            = FinancialTransaction::make($invoice->accounting_organization_id);
            $transaction->increase($accountingOrganization->receivableAccount, $invoice->getTotalAmount());

            foreach ($invoice->items as $item) {
                $transaction->increase($item->glAccount, $item->getSubTotal());
                $taxes = $item->getItemTax();
                if ($taxes > 0) {
                    $transaction->increase($accountingOrganization->taxPayableAccount, $taxes);
                }
            }

            $transaction->commit();

            return $invoice;
        });
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @throws \Throwable
     */
    private function createOverDueInvoices(Location $location): void
    {
        $this->createListInvoices(function ($userId) use ($location) {
            $invoice = $this->createInvoice($location, Carbon::now()->subDay());
            factory(InvoiceStatus::class)->create([
                'invoice_id' => $invoice->id,
                'user_id'    => $userId,
                'status'     => FinancialEntityStatuses::APPROVED,
            ]);

            $accountingOrganization = $invoice->accountingOrganization;
            $transaction            = FinancialTransaction::make($invoice->accounting_organization_id);
            $transaction->increase($accountingOrganization->receivableAccount, $invoice->getTotalAmount());

            foreach ($invoice->items as $item) {
                $transaction->increase($item->glAccount, $item->getSubTotal());
                $taxes = $item->getItemTax();
                if ($taxes > 0) {
                    $transaction->increase($accountingOrganization->taxPayableAccount, $taxes);
                }
            }

            $transaction->commit();

            return $invoice;
        });
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @throws \Throwable
     */
    private function createPaid(Location $location): void
    {
        $this->createListInvoices(function ($userId) use ($location) {
            $invoice = $this->createInvoice($location, Carbon::now()->subDay());
            factory(InvoiceStatus::class)->create([
                'invoice_id' => $invoice->id,
                'user_id'    => $userId,
                'status'     => FinancialEntityStatuses::APPROVED,
            ]);

            //Approve
            $accountingOrganization = $invoice->accountingOrganization;
            $transaction            = FinancialTransaction::make($invoice->accounting_organization_id);
            $transaction->increase($accountingOrganization->receivableAccount, $invoice->getTotalAmount());

            foreach ($invoice->items as $item) {
                $transaction->increase($item->glAccount, $item->getSubTotal());
                $taxes = $item->getItemTax();
                if ($taxes > 0) {
                    $transaction->increase($accountingOrganization->taxPayableAccount, $taxes);
                }
            }

            $transaction->commit();

            $glAccount = GLAccount::create([
                'accounting_organization_id' => $invoice->accounting_organization_id,
                'account_type_id'            => \App\Components\Finance\Models\AccountType::query()
                    ->where('name', 'Current Asset')
                    ->firstOrFail()
                    ->id,
                'tax_rate_id'                => TaxRate::query()
                    ->where('name', 'GST on Income')
                    ->firstOrFail()
                    ->id,
                'name'                       => 'Business cheque account',
                'status'                     => 'active',
                'is_active'                  => true,
            ]);

            $data = new \App\Components\Finance\Models\VO\CreateInvoicePaymentsData([
                'payment_data'  => [
                    'amount'                   => $invoice->getTotalAmount(),
                    'paidAt'                   => Carbon::now(),
                    'reference'                => 'Payment for invoice',
                    'accountingOrganizationId' => $invoice->accountingOrganization->id,
                    'payableGLAccountList'     => [
                        [
                            'glAccount' => $glAccount->id,
                            'amount'    => $invoice->getTotalAmount(),
                        ],
                    ],
                    'receivableGLAccountList'  => [
                        [
                            'glAccount' => $invoice->accountingOrganization->accounts_receivable_account_id,
                            'amount'    => $invoice->getTotalAmount(),
                        ],
                    ],
                ],
                'invoices_list' => [
                    [
                        'invoice_id' => $invoice->id,
                        'amount'     => $invoice->getTotalAmount(),
                    ],
                ],
            ]);

            app(\App\Components\Finance\Services\InvoicesService::class)->payWithDirectDepositPayment($data);

            return $invoice;
        });
    }

    /**
     * @param callable $callback
     */
    private function createListInvoices(callable $callback)
    {
        $users = User::query()->get();
        if (0 === $users->count()) {
            $users = factory(User::class, $this->faker->numberBetween(1, 2))->create();
        }

        $countOfInvoices = $this->faker->numberBetween(1, 2);
        for ($i = 0; $i < $countOfInvoices; $i++) {
            $callback($users->random()->id);
        }
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     * @param \Illuminate\Support\Carbon|null           $dueAt
     *
     * @return \App\Components\Finance\Models\Invoice
     *
     * @throws \Throwable
     */
    private function createInvoice(Location $location, Carbon $dueAt = null): Invoice
    {
        $contact = $this->createFullContact();

        if (null === $dueAt) {
            $dueAt = Carbon::now()->addMonth();
        }

        $invoice = new Invoice([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->getAccountingOrganization($location)->id,
            'recipient_contact_id'       => $contact->id,
            'recipient_address'          => $contact->getMailingAddress()->full_address,
            'recipient_name'             => $contact->getContactName(),
            'due_at'                     => $dueAt,
            'date'                       => Carbon::now(),
        ]);
        $invoice->saveOrFail();

        $numberOfItems = $this->faker->numberBetween(2, 4);
        for ($i = 0; $i < $numberOfItems; $i++) {
            $this->createInvoiceItem($invoice);
        }

        return $invoice;
    }

    /**
     * @param \App\Components\Finance\Models\Invoice $invoice
     *
     * @throws \Throwable
     */
    private function createInvoiceItem(Invoice $invoice): void
    {
        $taxRate = $this->getTaxRate();

        $gsCodes = GSCode::query()->get();
        if (0 === $gsCodes->count()) {
            /** @var GSCode $gsCode */
            $gsCode = factory(GSCode::class)->create();
        } else {
            $gsCode = $gsCodes->random();
        }

        $glAccount = $this->getGlAccount($invoice->accountingOrganization->id);

        $invoiceItem = new InvoiceItem([
            'invoice_id'    => $invoice->id,
            'gs_code_id'    => $gsCode->id,
            'description'   => $this->faker->word,
            'unit_cost'     => $this->faker->randomFloat(2, 10, 500),
            'quantity'      => $this->faker->numberBetween(1, 3),
            'discount'      => $this->faker->randomFloat(2, 0, 10),
            'gl_account_id' => $glAccount->id,
            'tax_rate_id'   => $taxRate->id,
        ]);
        $invoiceItem->saveOrFail();
    }

    private function getGlAccount(int $accountingOrganizationId)
    {
        $glAccounts = GLAccount::query()
            ->where('accounting_organization_id', $accountingOrganizationId)
            ->whereHas('accountType', function (\Illuminate\Database\Eloquent\Builder $query) {
                return $query->where('increase_action_is_debit', false);
            })
            ->get();

        if ($glAccounts->isEmpty()) {
            $job = new CreateDefaultGLAccounts($accountingOrganizationId);
            $job->handle();

            return $this->getGlAccount($accountingOrganizationId);
        }

        return $glAccounts->random();
    }

    /**
     * @return \App\Components\Finance\Models\TaxRate
     */
    private function getTaxRate(): TaxRate
    {
        $taxRate = TaxRate::query()
            ->where('name', $this->faker->randomElement([TaxRates::GST_ON_INCOME, TaxRates::GST_FREE_INCOME]))
            ->first();

        if (null === $taxRate) {
            Artisan::call('db:seed', [
                '--class' => 'TaxRatesSeeder',
            ]);

            $taxRate = $this->getTaxRate();
        }

        return $taxRate;
    }

    private function createAccountTypes()
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'AccountTypeGroupsSeeder',
            ]);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @return \App\Components\Finance\Models\AccountingOrganization
     *
     * @throws \Throwable
     */
    private function getAccountingOrganization(Location $location): AccountingOrganization
    {
        $organization = $this->accountingOrganizationService
            ->findActiveAccountOrganizationByLocation($location->id);

        if (null !== $organization) {
            return $organization;
        }

        return $this->createAccountingOrganization($location);
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @return \App\Components\Finance\Models\AccountingOrganization
     *
     * @throws \Throwable
     */
    private function createAccountingOrganization(Location $location)
    {
        $contact = $this->createFullContact();
        /** @var AccountingOrganization $organization */
        $organization = factory(AccountingOrganization::class)->create([
            'contact_id'        => $contact->id,
            'lock_day_of_month' => Carbon::now()->addWeek()->day,
        ]);

        $job = new CreateDefaultGLAccounts($organization->id);
        $job->handle();

        $this->accountingOrganizationService->addLocation($organization->id, $location->id);

        return $organization;
    }

    private function getContactCategory()
    {
        $name     = $this->faker->randomElement(ContactCategoryTypes::values());
        $name     = ucfirst(str_ireplace('_', ' ', $name));
        $category = \App\Components\Contacts\Models\ContactCategory::query()
            ->where('name', $name)
            ->first();

        if (null === $category) {
            Artisan::call('db:seed', [
                '--class' => 'ContactsSeeder',
            ]);

            $category = $this->getContactCategory();
        }

        return $category;
    }

    /**
     * @return \App\Components\Contacts\Models\Contact
     */
    private function createFullContact()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type'        => ContactTypes::COMPANY,
            'contact_category_id' => $this->getContactCategory()->id,
        ]);
        $address = $this->createAddress();

        $contact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        return $contact;
    }

    /**
     * @return \App\Components\Addresses\Models\Address
     */
    private function createAddress(): Address
    {
        $suburbs = Suburb::query()->get();
        if (0 === $suburbs->count()) {
            $suburb = factory(Suburb::class)->create();
        } else {
            $suburb = $suburbs->random();
        }

        return factory(Address::class)->create([
            'suburb_id' => $suburb,
        ]);
    }
}
