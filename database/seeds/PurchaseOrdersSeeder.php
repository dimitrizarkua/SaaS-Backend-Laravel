<?php

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Suburb;
use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\TaxRates;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Interfaces\PurchaseOrderCountersDataProviderInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderApproveRequest;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Locations\Models\Location;
use App\Components\Search\Models\UserAndTeam;
use App\Jobs\Finance\RecalculateCounters;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseOrdersSeeder
 */
class PurchaseOrdersSeeder extends Seeder
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
     * PurchaseOrdersSeeder constructor.
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
            PurchaseOrder::disableSearchSyncing();
            Contact::disableSearchSyncing();
            User::disableSearchSyncing();
            Suburb::disableSearchSyncing();
            UserAndTeam::disableSearchSyncing();
            DB::transaction(function () use ($locationList) {
                foreach ($locationList as $location) {
                    $this->createDraftPO($location);
                    $this->createPendingApprovalPO($location);
                    $this->createApprovedPO($location);
                }
            });
        } finally {
            PurchaseOrder::enableSearchSyncing();
            Contact::enableSearchSyncing();
            User::enableSearchSyncing();
            Suburb::enableSearchSyncing();
            UserAndTeam::enableSearchSyncing();
        }

        $purchaseOrderDataProvider = app()->make(PurchaseOrderCountersDataProviderInterface::class);
        $locationIds               = $locationList->pluck('id')->toArray();

        RecalculateCounters::dispatch($purchaseOrderDataProvider, $locationIds);
        Artisan::call('scout:import', ['model' => PurchaseOrder::class]);
        Artisan::call('scout:import', ['model' => User::class]);
        Artisan::call('scout:import', ['model' => Contact::class]);
        Artisan::call('scout:import', ['model' => Suburb::class]);
        Artisan::call('scout:import', ['model' => UserAndTeam::class]);
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @throws \Throwable
     */
    private function createDraftPO(Location $location): void
    {
        $this->createListPO(function ($userId) use ($location) {
            $purchaseOrder = $this->createPurchaseOrder($location);
            factory(PurchaseOrderStatus::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
                'user_id'           => $userId,
                'status'            => FinancialEntityStatuses::DRAFT,
            ]);

            return $purchaseOrder;
        });
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @throws \Throwable
     */
    private function createPendingApprovalPO(Location $location): void
    {
        $this->createListPO(function ($userId) use ($location) {
            $purchaseOrder = $this->createPurchaseOrder($location);
            factory(PurchaseOrderStatus::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
                'user_id'           => $userId,
                'status'            => FinancialEntityStatuses::DRAFT,
            ]);
            factory(PurchaseOrderApproveRequest::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
                'requester_id'      => $userId,
                'approver_id'       => $userId,
                'approved_at'       => null,
            ]);

            return $purchaseOrder;
        });
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @throws \Throwable
     */
    private function createApprovedPO(Location $location): void
    {
        $this->createListPO(function ($userId) use ($location) {
            $purchaseOrder = $this->createPurchaseOrder($location);
            factory(PurchaseOrderStatus::class)->create([
                'purchase_order_id' => $purchaseOrder->id,
                'user_id'           => $userId,
                'status'            => FinancialEntityStatuses::APPROVED,
            ]);

            return $purchaseOrder;
        });
    }

    /**
     * @param callable $callback
     */
    private function createListPO(callable $callback)
    {
        $users = User::query()->get();
        if (0 === $users->count()) {
            $users = factory(User::class, $this->faker->numberBetween(1, 2))->create();
        }

        $countOfPO = $this->faker->numberBetween(1, 2);
        for ($i = 0; $i < $countOfPO; $i++) {
            $callback($users->random()->id);
        }
    }

    /**
     * @param \App\Components\Locations\Models\Location $location
     *
     * @return PurchaseOrder
     *
     * @throws \Throwable
     */
    private function createPurchaseOrder(Location $location): PurchaseOrder
    {
        $contact = $this->createFullContact();

        $purchaseOrder = new PurchaseOrder([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->getAccountingOrganization($location)->id,
            'recipient_contact_id'       => $contact->id,
            'recipient_address'          => $contact->getMailingAddress()->full_address,
            'recipient_name'             => $contact->getContactName(),
            'date'                       => Carbon::now(),
        ]);
        $purchaseOrder->saveOrFail();

        $numberOfItems = $this->faker->numberBetween(2, 4);
        for ($i = 0; $i < $numberOfItems; $i++) {
            $this->createPOItem($purchaseOrder);
        }

        return $purchaseOrder;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
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

        $paymentDetailsAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $organization->id,
        ]);

        $organization->payment_details_account_id = $paymentDetailsAccount->id;
        $organization->saveOrFail();
        $this->accountingOrganizationService->addLocation($organization->id, $location->id);

        return $organization;
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     *
     * @throws Throwable
     */
    private function createPOItem(PurchaseOrder $purchaseOrder): void
    {
        $taxRate = $this->getTaxRate();

        $gsCodes = GSCode::query()->get();
        if (0 === $gsCodes->count()) {
            /** @var GSCode $gsCode */
            $gsCode = factory(GSCode::class)->create();
        } else {
            $gsCode = $gsCodes->random();
        }

        $organization = $purchaseOrder->accountingOrganization;
        if (null === $organization->receivableAccount) {
            $glAccount = factory(GLAccount::class)->create([
                'accounting_organization_id' => $organization->id,
            ]);

            $organization->accounts_receivable_account_id = $glAccount->id;
            $organization->saveOrFail();
        } else {
            $glAccount = $organization->receivableAccount;
        }

        $purchaseOrderItem = new PurchaseOrderItem([
            'purchase_order_id' => $purchaseOrder->id,
            'gs_code_id'        => $gsCode->id,
            'description'       => $this->faker->word,
            'unit_cost'         => $this->faker->randomFloat(2, 10, 500),
            'quantity'          => $this->faker->numberBetween(1, 3),
            'markup'            => $this->faker->numberBetween(1, 100),
            'gl_account_id'     => $glAccount->id,
            'tax_rate_id'       => $taxRate->id,
        ]);
        $purchaseOrderItem->saveOrFail();
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
}
