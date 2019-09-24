<?php

namespace Tests\API\Locations;

use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationSuburb;
use App\Components\Locations\Models\LocationUser;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class LocationsControllerTest
 *
 * @package Tests\API\Locations
 * @group   locations
 * @group   api
 */
class LocationsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'locations.view',
        'locations.create',
        'locations.update',
        'locations.modify_members',
        'locations.modify_suburbs',
    ];

    public function testGetList()
    {
        $instancesCount = $this->faker->numberBetween(1, 5);
        factory(Location::class, $instancesCount)->create();

        $url = action('Locations\LocationsController@index');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonCount($instancesCount, 'data');
    }

    public function testGetOneRecord()
    {
        /** @var Location $instance */
        $instance = factory(Location::class)->create();

        $url = action('Locations\LocationsController@show', ['location_id' => $instance->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($instance->id)
            ->assertSee($instance->code)
            ->assertSee($instance->name);
    }

    public function testNotFoundResponseWhenGettingNotExistingRecord()
    {
        $url = action('Locations\LocationsController@show', ['location_id' => 0]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(404);
    }

    public function testCreateRecord()
    {
        $url  = action('Locations\LocationsController@store');
        $data = [
            'code' => $this->faker->unique()->regexify('[A-Z]{2,5}'),
            'name' => $this->faker->unique()->city,
        ];
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(201)
            ->assertSeeData()
            ->assertSee($data['code'])
            ->assertSee($data['name']);

        $recordId = $response->getData()['id'];

        $instance = Location::findOrFail($recordId);
        self::assertEquals($data['code'], $instance->code);
        self::assertEquals($data['name'], $instance->name);
    }

    public function testUpdateRecord()
    {
        /** @var Location $instance */
        $instance = factory(Location::class)->create();

        $url  = action('Locations\LocationsController@update', ['location_id' => $instance->id]);
        $data = [
            'code' => $this->faker->unique()->regexify('[A-Z]{2,5}'),
            'name' => $this->faker->unique()->city,
        ];
        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);

        $response->assertStatus(200);

        $instance = Location::findOrFail($instance->id);
        self::assertEquals($data['code'], $instance->code);
        self::assertEquals($data['name'], $instance->name);
    }

    public function testGetSuburbsList()
    {
        /** @var State $state */
        $state        = factory(State::class)->create();
        $suburbsCount = $this->faker->numberBetween(1, 5);

        $suburbs = factory(Suburb::class, $suburbsCount)->create(['state_id' => $state->id,]);

        /** @var Location $location */
        $location = factory(Location::class)->create();

        /** @var Suburb $suburb */
        foreach ($suburbs as $suburb) {
            factory(LocationSuburb::class)->create([
                'location_id' => $location->id,
                'suburb_id'   => $suburb->id,
            ]);
        }

        $url = action('Locations\LocationsController@getSuburbs', ['location_id' => $location->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($suburbsCount, 'data');
    }

    public function testAddSuburbToLocation()
    {
        /** @var Suburb $suburb */
        $suburb = factory(Suburb::class)->create();
        /** @var Location $location */
        $location = factory(Location::class)->create();

        $url = action('Locations\LocationsController@addSuburb', [
            'location_id' => $location->id,
            'suburb_id'   => $suburb->id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(200);

        LocationSuburb::query()->where([
            'location_id' => $location->id,
            'suburb_id'   => $suburb->id,
        ])->firstOrFail();
    }

    public function testRemoveSuburbFromLocation()
    {
        /** @var LocationSuburb $locationSuburb */
        $locationSuburb = factory(LocationSuburb::class)->create();

        $url = action('Locations\LocationsController@removeSuburb', [
            'location_id' => $locationSuburb->location_id,
            'suburb_id'   => $locationSuburb->suburb_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        LocationSuburb::query()->where([
            'location_id' => $locationSuburb->location_id,
            'suburb_id'   => $locationSuburb->suburb_id,
        ])->firstOrFail();
    }

    public function testGetUsersList()
    {
        $usersCount = $this->faker->numberBetween(1, 5);
        $users      = factory(User::class, $usersCount)->create();

        /** @var Location $location */
        $location = factory(Location::class)->create();

        /** @var \App\Models\User $user */
        foreach ($users as $user) {
            factory(LocationUser::class)->create([
                'location_id' => $location->id,
                'user_id'     => $user->id,
            ]);
        }

        $url = action('Locations\LocationsController@getUsers', ['location_id' => $location->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($usersCount, 'data');
    }

    public function testAddUserToLocation()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Location $location */
        $location = factory(Location::class)->create();

        $url = action('Locations\LocationsController@addUser', [
            'location_id' => $location->id,
            'user_id'     => $user->id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(200);

        LocationUser::query()->where([
            'location_id' => $location->id,
            'user_id'     => $user->id,
        ])->firstOrFail();
    }

    public function testRemoveUserFromLocation()
    {
        /** @var LocationUser $locationUser */
        $locationUser = factory(LocationUser::class)->create();

        $url = action('Locations\LocationsController@removeUser', [
            'location_id' => $locationUser->location_id,
            'user_id'     => $locationUser->user_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        LocationUser::query()->where([
            'location_id' => $locationUser->location_id,
            'user_id'     => $locationUser->user_id,
        ])->firstOrFail();
    }
}
