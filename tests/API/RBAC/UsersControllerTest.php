<?php

namespace Tests\API\RBAC;

use App\Components\Contacts\Models\Contact;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Components\Users\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\API\ApiTestCase;

/**
 * Class UsersControllerTest
 *
 * @package Tests\API\RBAC
 *
 * @group   users
 */
class UsersControllerTest extends ApiTestCase
{
    protected $permissions = ['users.update'];

    public function testUpdateUser(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();

        $locationsCount = $this->faker->numberBetween(4, 5);
        factory(LocationUser::class, $locationsCount)->create([
            'user_id' => $user->id,
        ]);

        $locationsCount = $this->faker->numberBetween(1, 3);
        $locationIds    = factory(Location::class, $locationsCount)->create()->pluck('id')->toArray();

        $data = [
            'first_name'                   => $this->faker->firstName,
            'last_name'                    => $this->faker->lastName,
            'password'                     => $this->faker->password,
            'invoice_approve_limit'        => $this->faker->randomFloat(2, 100, 1000),
            'purchase_order_approve_limit' => $this->faker->randomFloat(2, 100, 1000),
            'credit_note_approval_limit'   => $this->faker->randomFloat(2, 100, 1000),
            'locations'                    => $locationIds,
            'contact_id'                   => $contact->id,
        ];

        $action = action('RBAC\UsersController@update', [
            'id' => $user->id,
        ]);

        $this->patchJson($action, $data)
            ->assertStatus(200)
            ->assertValidSchema(UserProfileResource::class);

        $reloaded = User::find($user->id);
        self::assertEquals($data['first_name'], $reloaded->first_name);
        self::assertEquals($data['last_name'], $reloaded->last_name);
        self::assertTrue(Hash::check($data['password'], $reloaded->password));
        self::assertEquals($data['invoice_approve_limit'], $reloaded->invoice_approve_limit);
        self::assertEquals($data['purchase_order_approve_limit'], $reloaded->purchase_order_approve_limit);
        self::assertEquals($data['credit_note_approval_limit'], $reloaded->credit_note_approval_limit);
        self::assertCount($locationsCount, $reloaded->locations()->get());
        self::assertEquals($data['contact_id'], $reloaded->contact_id);
    }

    public function testUpdateUserDetachContact(): void
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        /** @var User $user */
        $user   = factory(User::class)->create([
            'contact_id' => $contact->id,
        ]);
        $data   = [
            'contact_id' => null,
        ];
        $action = action('RBAC\UsersController@update', [
            'id' => $user->id,
        ]);

        $this->patchJson($action, $data)
            ->assertStatus(200);

        $reloaded = User::find($user->id);
        self::assertNull($reloaded->contact_id);
    }

    public function testUpdateUserLocationTouchUser(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create([
            'updated_at' => now()->subDay(),
        ]);

        $locationsCount = $this->faker->numberBetween(4, 5);
        factory(LocationUser::class, $locationsCount)->create([
            'user_id' => $user->id,
        ]);

        $locationsCount = $this->faker->numberBetween(1, 3);
        $locationIds    = factory(Location::class, $locationsCount)->create()->pluck('id')->toArray();

        $data = [
            'locations' => $locationIds,
        ];

        $action = action('RBAC\UsersController@update', [
            'id' => $user->id,
        ]);

        $this->patchJson($action, $data)
            ->assertStatus(200);

        $reloaded = User::find($user->id);
        self::assertCount($locationsCount, $reloaded->locations()->get());
        self::assertTrue($reloaded->updated_at->gt($user->updated_at));
    }

    public function testFailToUpdateLimitsWhenLimitIsTooBig(): void
    {
        /** @var User $user */
        $user           = factory(User::class)->create();
        $incorrectFloat = $this->faker->randomFloat(2, 100000000);

        $data = [
            'invoice_approve_limit'        => $incorrectFloat,
            'purchase_order_approve_limit' => $incorrectFloat,
            'credit_note_approval_limit'   => $incorrectFloat,
        ];

        $action = action('RBAC\UsersController@update', [
            'id' => $user->id,
        ]);

        $this->patchJson($action, $data)
            ->assertStatus(422);
    }
}
