<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\AccountType;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\TaxRate;
use App\Components\Finance\Resources\CreditNoteItemResource;
use App\Components\Finance\Services\CreditNoteService;
use App\Components\Locations\Models\Location;
use App\Jobs\Finance\RecalculateCounters;
use App\Models\User;
use Tests\API\ApiTestCase;

/**
 * Class CreditNoteItemsControllerTest
 *
 * @package App\Http\Controllers\Finance
 *
 * @group   finance
 * @group   credit-note
 */
class CreditNoteItemsControllerTest extends ApiTestCase
{

    protected $permissions = [
        'finance.credit_notes.manage',
        'finance.credit_notes.view',
        'finance.credit_notes.manage_locked',
    ];

    /**
     * @var AccountingOrganization
     */
    private $accountOrganization;
    /**
     * @var GLAccount
     */
    private $receivableAccount;
    /**
     * @var GLAccount
     */
    private $salesAccount;
    /**
     * @var GLAccount
     */
    private $taxAccount;

    public function setUp()
    {
        parent::setUp();
        $models       = [
            CreditNoteItem::class,
            CreditNote::class,
        ];
        $this->models = array_merge($models, $this->models);

        /** @var AccountingOrganization $accountOrganiztion */
        $this->accountOrganization = factory(AccountingOrganization::class)->create();
        $assetsAccountType         = factory(AccountType::class)->create([
            'name'                     => 'Asset',
            'increase_action_is_debit' => true,
        ]);
        $revenueAccountType        = factory(AccountType::class)->create([
            'name'                     => 'Revenue',
            'increase_action_is_debit' => false,
        ]);
        $liabilityAccountType      = factory(AccountType::class)->create([
            'name'                     => 'Liability',
            'increase_action_is_debit' => false,
        ]);
        $this->receivableAccount   = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'account_type_id'            => $assetsAccountType->id,
        ]);
        $this->salesAccount        = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'account_type_id'            => $revenueAccountType->id,
        ]);
        $this->taxAccount          = factory(GLAccount::class)->create([
            'accounting_organization_id' => $this->accountOrganization->id,
            'account_type_id'            => $liabilityAccountType->id,
        ]);
        $this->accountOrganization->update(
            [
                'accounts_receivable_account_id' => $this->receivableAccount->id,
                'tax_payable_account_id'         => $this->taxAccount->id,
            ]
        );
    }

    public function testCreateMethod()
    {
        $data = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'gl_account_id' => factory(GLAccount::class)->create()->id,
            'tax_rate_id'   => factory(TaxRate::class)->create()->id,
            'description'   => $this->faker->text,
            'quantity'      => $this->faker->numberBetween(1, 5),
            'unit_cost'     => $this->faker->randomFloat(2, 10, 1000),
            'position'      => $this->faker->numberBetween(1, 10),
        ];

        $this->expectsJobs(RecalculateCounters::class);
        $url      = action('Finance\CreditNoteItemsController@store', [
            'credit_note_id' => factory(CreditNote::class)->create()->id,
        ]);
        $response = $this->postJson($url, $data);

        $response->assertStatus(201)
            ->assertValidSchema(CreditNoteItemResource::class);
    }

    public function testUpdateMethod()
    {
        $model = factory(CreditNoteItem::class)->create();
        $data  = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'gl_account_id' => factory(GLAccount::class)->create()->id,
            'tax_rate_id'   => factory(TaxRate::class)->create()->id,
            'description'   => $this->faker->text,
            'quantity'      => $this->faker->numberBetween(1, 5),
            'unit_cost'     => $this->faker->randomFloat(2, 10, 1000),
            'position'      => $this->faker->numberBetween(1, 10),
        ];

        $this->expectsJobs(RecalculateCounters::class);
        $url = action('Finance\CreditNoteItemsController@update', [
            'credit_note_id'      => factory(CreditNote::class)->create()->id,
            'credit_note_item_id' => $model->id,
        ]);
        $this->patchJson($url, $data)
            ->assertStatus(200);

        $creditNoteItem = CreditNoteItem::find($model->id);

        self::assertEquals($creditNoteItem->gs_code_id, $data['gs_code_id']);
        self::assertEquals($creditNoteItem->gl_account_id, $data['gl_account_id']);
        self::assertEquals($creditNoteItem->tax_rate_id, $data['tax_rate_id']);
        self::assertEquals($creditNoteItem->description, $data['description']);
        self::assertEquals($creditNoteItem->quantity, $data['quantity']);
        self::assertEquals($creditNoteItem->unit_cost, $data['unit_cost']);
        self::assertEquals($creditNoteItem->position, $data['position']);
    }

    public function testUpdateMethodWithApprovedCreditNote()
    {
        $location        = factory(Location::class)->create();
        $creditNote      = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        $creditNoteItems = factory(CreditNoteItem::class, 3)->create(
            [
                'credit_note_id' => $creditNote->id,
                'gl_account_id'  => $this->salesAccount->id,
            ]
        );

        $user = factory(User::class)->create([
            'credit_note_approval_limit' => $creditNote->getTotalAmount(),
        ]);
        $user->locations()->attach($location);
        $this->app->get(CreditNoteService::class)->approve($creditNote->id, $user);
        $data = [
            'gs_code_id'    => factory(GSCode::class)->create()->id,
            'gl_account_id' => factory(GLAccount::class)->create()->id,
            'tax_rate_id'   => factory(TaxRate::class)->create()->id,
            'description'   => $this->faker->text,
            'quantity'      => $this->faker->numberBetween(1, 5),
            'unit_cost'     => $this->faker->randomFloat(2, 10, 1000),
            'position'      => $this->faker->numberBetween(1, 10),
        ];

        $url = action('Finance\CreditNoteItemsController@update', [
            'credit_note_id'      => $creditNote->id,
            'credit_note_item_id' => $creditNoteItems->first()->id,
        ]);
        $this->patchJson($url, $data)
            ->assertStatus(405);
    }

    public function testDeleteMethod()
    {
        $creditNote     = factory(CreditNote::class)->create();
        $creditNoteItem = factory(CreditNoteItem::class)->create(['credit_note_id' => $creditNote->id]);
        $url            = action('Finance\CreditNoteItemsController@destroy', [
            'credit_note_id'      => $creditNote->id,
            'credit_note_item_id' => $creditNoteItem->id,
        ]);

        $this->expectsJobs(RecalculateCounters::class);
        $response = $this->deleteJson($url);
        $response->assertStatus(200);
        self::assertNull(CreditNoteItem::find($creditNoteItem->id));
    }

    public function testDeleteMethodWithLockedCreditNote()
    {
        $location        = factory(Location::class)->create();
        $creditNote      = factory(CreditNote::class)->create([
            'location_id'                => $location->id,
            'accounting_organization_id' => $this->accountOrganization->id,
        ]);
        $creditNoteItems = factory(CreditNoteItem::class, 3)->create([
            'credit_note_id' => $creditNote->id,
            'gl_account_id'  => $this->salesAccount->id,
        ]);

        $user = factory(User::class)->create(['credit_note_approval_limit' => $creditNote->getTotalAmount()]);
        $user->locations()->attach($location);
        $this->app->get(CreditNoteService::class)->approve($creditNote->id, $user);

        $url      = action('Finance\CreditNoteItemsController@destroy', [
            'credit_note_id'      => $creditNote->id,
            'credit_note_item_id' => $creditNoteItems->first()->id,
        ]);
        $response = $this->deleteJson($url);

        $response->assertStatus(405);
        self::assertNotNull(CreditNoteItem::find($creditNoteItems->first()->id));
    }
}
