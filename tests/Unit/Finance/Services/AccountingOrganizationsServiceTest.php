<?php

namespace Tests\Unit\Finance\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Models\VO\CreateAccountingOrganizationData;
use App\Components\Locations\Models\Location;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Class AccountingOrganizationsServiceTest
 *
 * @package Tests\Unit\Finance\Services
 *
 * @group   services
 * @group   finance
 * @group   accounting-organization
 */
class AccountingOrganizationsServiceTest extends TestCase
{
    /**
     * @var AccountingOrganizationsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $models = [
            TaxRate::class,
            AccountType::class,
            GLAccount::class,
            AccountingOrganization::class,
            Location::class,
        ];

        $this->models  = array_merge($models, $this->models);
        $this->service = $this->app->get(AccountingOrganizationsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->service);
    }

    public function testGetAccountMethod()
    {
        $model = factory(AccountingOrganization::class)->create();

        $retrievedModel = $this->service->getAccountingOrganization($model->id);
        self::assertEquals($retrievedModel->contact_id, $model->contact_id);
    }

    public function testGetAccountMethodShouldThrowException()
    {
        self::expectException(ModelNotFoundException::class);
        $this->service->getAccountingOrganization(0);
    }

    public function testAttachLocation()
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create();
        $location               = factory(Location::class)->create();

        self::assertEmpty($accountingOrganization->locations);

        $this->service->addLocation($accountingOrganization->id, $location->id);

        $reloaded = AccountingOrganization::findOrFail($accountingOrganization->id);
        self::assertCount(1, $reloaded->locations);
    }

    public function testPreviousOrganizationShouldBeDeactivatedWhileAttachingNewOne()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var AccountingOrganization $firstOrg */
        $firstOrg = factory(AccountingOrganization::class)->create([
            'is_active' => true,
        ]);
        $firstOrg->locations()->attach($location);

        $secondOrg = factory(AccountingOrganization::class)->create([
            'is_active' => true,
        ]);

        $this->service->addLocation($secondOrg->id, $location->id);
        $reloadedFirst = AccountingOrganization::findOrFail($firstOrg->id);
        self::assertFalse($reloadedFirst->is_active);
    }

    public function testDetachLocation()
    {
        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create();
        $location               = factory(Location::class)->create();

        $this->service->addLocation($accountingOrganization->id, $location->id);

        $reloaded = AccountingOrganization::findOrFail($accountingOrganization->id);
        self::assertCount(1, $reloaded->locations);

        $this->service->removeLocation($accountingOrganization->id, $location->id);

        $reloaded = AccountingOrganization::findOrFail($accountingOrganization->id);
        self::assertEmpty($reloaded->locations);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testCreate()
    {
        $location = factory(Location::class)->create();
        /** @var \App\Components\Contacts\Models\Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $lockDay = Carbon::now()->addDay()->day;
        $data    = new CreateAccountingOrganizationData([
            'contact_id'        => $contact->id,
            'lock_day_of_month' => $lockDay,
            'location_id'       => $location->id,
        ]);

        $accountingOrganization = $this->service->create($data);

        self::assertEquals($contact->id, $accountingOrganization->contact_id);
        self::assertCount(1, $accountingOrganization->locations);
        self::assertEquals($location->id, $accountingOrganization->locations->first()->id);
        self::assertEquals($lockDay, $accountingOrganization->lock_day_of_month);
        self::assertTrue($accountingOrganization->is_active);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testPreviousOrganizationShouldBeDeactivated()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var AccountingOrganization $firstOrg */
        $firstOrg = factory(AccountingOrganization::class)->create([
            'is_active' => true,
        ]);
        $firstOrg->locations()->attach($location);

        $contact   = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $lockDay   = Carbon::now()->addDay()->day;
        $data      = new CreateAccountingOrganizationData([
            'contact_id'        => $contact->id,
            'lock_day_of_month' => $lockDay,
            'location_id'       => $location->id,
        ]);
        $secondOrg = $this->service->create($data);

        $reloadedFirst = AccountingOrganization::findOrFail($firstOrg->id);
        self::assertTrue($secondOrg->is_active);
        self::assertFalse($reloadedFirst->is_active);
    }
}
