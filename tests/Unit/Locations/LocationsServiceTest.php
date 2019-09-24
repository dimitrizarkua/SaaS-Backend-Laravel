<?php

namespace Tests\Unit\Locations;

use App\Components\Addresses\Models\Suburb;
use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationSuburb;
use App\Components\Locations\Models\LocationUser;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

/**
 * Class LocationsServiceTest
 *
 * @package Tests\Unit\Locations
 * @group   locations
 * @group   locations-service
 */
class LocationsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Locations\Interfaces\LocationsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()
            ->make(LocationsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testAddUserPrimaryLocation()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->service->addUser($location->id, $user->id, true);

        /** @var LocationUser $locationUser */
        $locationUser = LocationUser::query()->where([
            'location_id' => $location->id,
            'user_id'     => $user->id,
        ])->firstOrFail();

        self::assertEquals($location->id, $locationUser->location_id);
        self::assertEquals($user->id, $locationUser->user_id);
        self::assertTrue($locationUser->primary);
    }

    public function testAddUserPrimaryLocationAndAfterThatAddNewOnePrimaryLocation()
    {
        /** @var Location $firstLocation */
        $firstLocation = factory(Location::class)->create();
        /** @var Location $secondLocation */
        $secondLocation = factory(Location::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->service->addUser($firstLocation->id, $user->id, true);

        /** @var LocationUser $firstLocationUser */
        $firstLocationUser = LocationUser::query()->where([
            'location_id' => $firstLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();

        self::assertTrue($firstLocationUser->primary);

        $this->service->addUser($secondLocation->id, $user->id, true);

        /** @var LocationUser $reloadedFirstLocationUser */
        $reloadedFirstLocationUser = LocationUser::query()->where([
            'location_id' => $firstLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();
        /** @var LocationUser $secondLocationUser */
        $secondLocationUser = LocationUser::query()->where([
            'location_id' => $secondLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();

        self::assertFalse($reloadedFirstLocationUser->primary);
        self::assertTrue($secondLocationUser->primary);
    }

    public function testChangeUserPrimaryLocation()
    {
        /** @var Location $firstLocation */
        $firstLocation = factory(Location::class)->create();
        /** @var Location $secondLocation */
        $secondLocation = factory(Location::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->service->addUser($firstLocation->id, $user->id, true);

        /** @var LocationUser $firstLocationUser */
        $firstLocationUser = LocationUser::query()->where([
            'location_id' => $firstLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();

        self::assertTrue($firstLocationUser->primary);

        $this->service->addUser($secondLocation->id, $user->id, false);

        /** @var LocationUser $reloadedFirstLocationUser */
        $reloadedFirstLocationUser = LocationUser::query()->where([
            'location_id' => $firstLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();
        /** @var LocationUser $secondLocationUser */
        $secondLocationUser = LocationUser::query()->where([
            'location_id' => $secondLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();

        self::assertTrue($reloadedFirstLocationUser->primary);
        self::assertFalse($secondLocationUser->primary);

        $this->service->addUser($secondLocation->id, $user->id, true);

        /** @var LocationUser $reloadedFirstLocationUser */
        $reloadedFirstLocationUser = LocationUser::query()->where([
            'location_id' => $firstLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();
        /** @var LocationUser $reloadedSecondLocationUser */
        $reloadedSecondLocationUser = LocationUser::query()->where([
            'location_id' => $secondLocation->id,
            'user_id'     => $user->id,
        ])->firstOrFail();

        self::assertFalse($reloadedFirstLocationUser->primary);
        self::assertTrue($reloadedSecondLocationUser->primary);
    }

    public function testUserMustHaveOnlyOnePrimaryLocation()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $locations = factory(Location::class, $this->faker->numberBetween(1, 5))->create();

        /** @var Location $location */
        foreach ($locations as $location) {
            $primary = $this->faker->boolean();
            factory(LocationUser::class)->create([
                'location_id' => $location->id,
                'user_id'     => $user->id,
                'primary'     => $primary,
            ]);
        }

        $locationUserCount = LocationUser::query()->where([
            'user_id' => $user->id,
            'primary' => true,
        ])->count();

        self::assertEquals(1, $locationUserCount);
    }

    public function testRemoveUser()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();
        factory(LocationUser::class)->create([
            'location_id' => $location->id,
            'user_id'     => $user->id,
        ]);

        $this->service->removeUser($location->id, $user->id);

        self::expectException(ModelNotFoundException::class);

        LocationUser::query()->where([
            'location_id' => $location->id,
            'user_id'     => $user->id,
        ])->firstOrFail();
    }

    public function testAddSuburb()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var Suburb $suburb */
        $suburb = factory(Suburb::class)->create();

        $this->service->addSuburb($location->id, $suburb->id);

        /** @var LocationSuburb $locationSuburb */
        $locationSuburb = LocationSuburb::query()->where([
            'location_id' => $location->id,
            'suburb_id'   => $suburb->id,
        ])->firstOrFail();

        self::assertEquals($location->id, $locationSuburb->location_id);
        self::assertEquals($suburb->id, $locationSuburb->suburb_id);
    }

    public function testRemoveSuburb()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        /** @var Suburb $suburb */
        $suburb = factory(Suburb::class)->create();
        factory(LocationSuburb::class)->create([
            'location_id' => $location->id,
            'suburb_id'   => $suburb->id,
        ]);

        $this->service->removeSuburb($location->id, $suburb->id);

        self::expectException(ModelNotFoundException::class);

        LocationSuburb::query()->where([
            'location_id' => $location->id,
            'suburb_id'   => $suburb->id,
        ])->firstOrFail();
    }
}
