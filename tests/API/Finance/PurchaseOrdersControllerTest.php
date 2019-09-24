<?php

namespace Tests\API\Finance;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Resources\PurchaseOrderResource;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Http\Responses\Finance\PurchaseOrderResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class PurchaseOrdersControllerTest
 *
 * @package Tests\API\Finance
 * @group   purchase-orders
 * @group   finance
 */
class PurchaseOrdersControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.purchase_orders.view',
        'finance.purchase_orders.manage',
    ];

    /**
     * @var $defaultUserApproveLimit float
     */
    private $defaultUserApproveLimit;

    public function setUp(): void
    {
        parent::setUp();

        $this->defaultUserApproveLimit = $this->faker->randomFloat(2, 1, 1000);
        $models                        = [
            PurchaseOrder::class,
            AccountType::class,
            GLAccount::class,
            TaxRate::class,
            AccountingOrganization::class,
            Location::class,
        ];

        $this->models = array_merge($models, $this->models);
    }

    public function testCreatePurchaseOrder(): void
    {
        $lockDay  = $this->faker->numberBetween(1, 15);
        $location = factory(Location::class)->create();

        /** @var AccountingOrganization $accountingOrganization */
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => $lockDay,
            'is_active'         => true,
        ]);
        $accountingOrganization->locations()->attach($location);
        $this->user->locations()->attach($location);

        /** @var Address $address */
        $address = factory(Address::class)->create();
        /** @var Contact $recipientContact */
        $recipientContact = factory(Contact::class)->create();
        $recipientContact->addresses()->attach($address, [
            'type' => AddressContactTypes::MAILING,
        ]);

        /** @var GLAccount $bankAccount */
        $bankAccount = factory(GLAccount::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
            'account_type_id'            => factory(AccountType::class)->create([
                'name'                     => 'Asset',
                'increase_action_is_debit' => true,
            ])->id,
        ]);

        $request = [
            'location_id'          => $location->id,
            'recipient_contact_id' => $recipientContact->id,
            'date'                 => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
            'items'                => [
                [
                    'gs_code_id'    => factory(GSCode::class)->create()->id,
                    'description'   => $this->faker->word,
                    'unit_cost'     => $this->faker->randomFloat(2, 100, 1000),
                    'quantity'      => $this->faker->numberBetween(1, 4),
                    'markup'        => $this->faker->randomFloat(2, 0, 1),
                    'gl_account_id' => $bankAccount->id,
                    'tax_rate_id'   => $bankAccount->tax_rate_id,
                ],
            ],
            'reference'            => $this->faker->word,
        ];
        $url     = action('Finance\PurchaseOrdersController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertValidSchema(PurchaseOrderResource::class);
        $data     = $response->getData();
        $model    = PurchaseOrder::findOrFail($data['id']);

        self::assertEquals($data['location_id'], $model->location_id);
        self::assertEquals($accountingOrganization->id, $model->accounting_organization_id);
        self::assertEquals($data['recipient_contact_id'], $model->recipient_contact_id);
        self::assertEquals($address->full_address, $model->recipient_address);
        self::assertEquals($recipientContact->getContactName(), $model->recipient_name);
        self::assertEmpty($model->job_id);
        self::assertEmpty($model->document_id);
        self::assertEquals($data['date'], $model->date->toDateString());
        self::assertEquals($request['reference'], $data['reference']);
        self::assertCount(1, $model->items);
    }

    public function testCreatePurchaseOrderShouldReturnValidationError(): void
    {
        $url = action('Finance\PurchaseOrdersController@store');

        $this->postJson($url)
            ->assertStatus(422);
    }

    public function testShowPurchaseOrder(): void
    {
        /** @var PurchaseOrder $model */
        $model = factory(PurchaseOrder::class)->create();
        $url   = action('Finance\PurchaseOrdersController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(PurchaseOrderResource::class);
        $data = $response->getData();

        self::assertEquals($data['id'], $model->id);
        self::assertEquals($data['location_id'], $model->location_id);
        self::assertEquals($data['accounting_organization_id'], $model->accounting_organization_id);
        self::assertEquals($data['recipient_contact_id'], $model->recipient_contact_id);
        self::assertEmpty($model->job_id);
        self::assertEmpty($model->document_id);
        self::assertEquals($data['date'], $model->date->toDateString());
    }

    public function testShowPurchaseOrderShouldReturnNotFound(): void
    {
        $url = action('Finance\PurchaseOrdersController@show', [
            'id' => 0,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testUpdatePurchaseOrder(): void
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        /** @var PurchaseOrder $model */
        $model = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $model->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $url     = action('Finance\PurchaseOrdersController@update', [
            'id' => $model->id,
        ]);
        $request = [
            'job_id' => factory(Job::class)->create()->id,
            'date'   => Carbon::now()->subDays(1)->toDateString(),
        ];

        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertValidSchema(PurchaseOrderResponse::class, true);

        $reloaded = PurchaseOrder::findOrFail($model->id);

        self::assertEquals($request['job_id'], $reloaded->job_id);
        self::assertEquals($request['date'], $reloaded->date->toDateString());
    }

    public function testUpdatePurchaseOrderReference(): void
    {
        $accountingOrganization = factory(AccountingOrganization::class)->create([
            'lock_day_of_month' => Carbon::now()->addDay()->day,
            'is_active'         => true,
        ]);
        /** @var PurchaseOrder $model */
        $model = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => $accountingOrganization->id,
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $model->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $url     = action('Finance\PurchaseOrdersController@update', [
            'id' => $model->id,
        ]);
        $request = [
            'reference' => $this->faker->word,
        ];

        $this->patchJson($url, $request)
            ->assertStatus(200);

        $reloaded = PurchaseOrder::findOrFail($model->id);
        self::assertEquals($request['reference'], $reloaded->reference);
    }

    public function testUpdatePurchaseOrderShouldReturnValidationError(): void
    {
        /** @var PurchaseOrder $model */
        $model   = factory(PurchaseOrder::class)->create();
        $url     = action('Finance\PurchaseOrdersController@update', [
            'id' => $model->id,
        ]);
        $request = [
            'location_id'                => null,
            'accounting_organization_id' => null,
            'recipient_contact_id'       => null,
        ];

        $this->patchJson($url, $request)
            ->assertStatus(422);
    }

    public function testDestroyPurchaseOrder(): void
    {
        /** @var PurchaseOrder $model */
        $model = factory(PurchaseOrder::class)->create();
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $model->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        $url = action('Finance\PurchaseOrdersController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        $this->expectException(ModelNotFoundException::class);
        PurchaseOrder::findOrFail($model->id);
    }

    public function testDestroyPurchaseOrderShouldReturnNotFound(): void
    {
        $url = action('Finance\PurchaseOrdersController@destroy', [
            'id' => 0,
        ]);

        $this->deleteJson($url)
            ->assertStatus(404);
    }

    public function testApprovePurchaseOrder(): void
    {
        $lockDay = $this->faker->numberBetween(1, 15);
        /** @var PurchaseOrder $model */
        $model = factory(PurchaseOrder::class)->create([
            'accounting_organization_id' => factory(AccountingOrganization::class)->create([
                'lock_day_of_month' => $lockDay,
            ])->id,
            'date'                       => Carbon::create(null, null, $lockDay)
                ->addDay()
                ->toDateString(),
        ]);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $model->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);
        factory(PurchaseOrderItem::class, 3)->create([
            'purchase_order_id' => $model->id,
        ]);

        $url = action('Finance\PurchaseOrdersController@approve', [
            'id' => $model->id,
        ]);
        $this->user->locations()->attach($model->location_id);
        $this->user->update([
            'purchase_order_approve_limit' => $model->getTotalAmount(),
        ]);
        $response = $this->postJson($url);
        $response->assertStatus(200);
        $reloaded = PurchaseOrder::findOrFail($model->id);

        self::assertTrue($reloaded->isApproved());
    }

    public function testGetSuggestedApprovers(): void
    {
        /** @var PurchaseOrder $model */
        $model           = factory(PurchaseOrder::class)->create();
        $numberOfRecords = $this->faker->numberBetween(1, 5);
        factory(LocationUser::class, $numberOfRecords)->create([
            'location_id' => $model->location_id,
            'user_id'     => function () {
                return factory(User::class)->create([
                    'purchase_order_approve_limit' => $this->defaultUserApproveLimit,
                ])->id;
            },
        ]);
        $url = action('Finance\PurchaseOrdersController@getSuggestedApprovers', [
            'id' => $model->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }
}
