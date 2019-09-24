<?php

namespace Tests\API\Operations;

use App\Components\Locations\Models\Location;
use App\Components\Operations\Models\JobRun;
use App\Components\Operations\Models\Vehicle;
use App\Components\Operations\Models\VehicleStatusType;
use Carbon\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class VehiclesControllerTest
 *
 * @package Tests\API\Operations
 * @group   jobs
 * @group   api
 */
class VehiclesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'operations.vehicles.view', 'operations.vehicles.manage', 'operations.vehicles.change_status',
    ];

    public function testListLocationVehicles()
    {
        $location = factory(Location::class)->create();

        $vehiclesCount = $this->faker->numberBetween(1, 5);
        factory(Vehicle::class, $vehiclesCount)->create([
            'location_id' => $location->id,
        ]);

        $url = action('Operations\VehiclesController@listLocationVehicles', [
            'location_id' => $location->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($vehiclesCount, 'data');
    }

    public function testListLocationVehiclesWithFilterByDate()
    {
        $location = factory(Location::class)->create();
        /** @var JobRun $jobRun */

        $vehiclesCount = $this->faker->numberBetween(1, 5);
        factory(Vehicle::class, $vehiclesCount)
            ->create([
                'location_id' => $location->id,
            ]);

        $url = action('Operations\VehiclesController@listLocationVehicles', [
            'location_id' => $location->id,
            'date'        => Carbon::now()->toDateString(),
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonCount($vehiclesCount, 'data');
    }

    public function testListLocationBookedVehiclesWithFilterByDate()
    {
        $location = factory(Location::class)->create();
        /** @var JobRun $jobRun */
        $jobRun = factory(JobRun::class)->create([
            'location_id' => $location->id,
            'date'        => Carbon::now()->toDateString(),
        ]);

        factory(Vehicle::class)
            ->create([
                'location_id' => $location->id,
            ])
            ->each(function (Vehicle $vehicle) use ($jobRun) {
                $jobRun->assignedVehicles()->attach($vehicle->id);
            });

        $url = action('Operations\VehiclesController@listLocationVehicles', [
            'location_id' => $location->id,
            'date'        => Carbon::now()->toDateString(),
        ]);

        $response = $this->getJson($url);
        $response = $response->assertStatus(200)
            ->getData();
        self::assertTrue($response[0]['is_booked']);
    }

    public function testListLocationNotBookedVehiclesWithFilterByDate()
    {
        $location = factory(Location::class)->create();

        factory(Vehicle::class)
            ->create([
                'location_id' => $location->id,
            ]);

        $url = action('Operations\VehiclesController@listLocationVehicles', [
            'location_id' => $location->id,
            'date'        => Carbon::now()->toDateString(),
        ]);

        $response = $this->getJson($url);
        $response = $response->assertStatus(200)
            ->getData();
        self::assertFalse($response[0]['is_booked']);
    }

    public function testListLocationExcludeEndedRentalVehiclesWithFilterByDate()
    {
        $location = factory(Location::class)->create();

        $endedRentVehicles = $this->faker->numberBetween(1, 5);
        $rentVehiclesCount = $this->faker->numberBetween(1, 5);
        $vehiclesCount     = $this->faker->numberBetween(1, 5);
        factory(Vehicle::class, $endedRentVehicles)
            ->create([
                'location_id'    => $location->id,
                'rent_starts_at' => Carbon::now()->subDays(10)->toDateString(),
                'rent_ends_at'   => Carbon::now()->subDays(5)->toDateString(),
            ]);
        factory(Vehicle::class, $endedRentVehicles)
            ->create([
                'location_id'    => $location->id,
                'rent_starts_at' => Carbon::now()->addDays(5)->toDateString(),
                'rent_ends_at'   => Carbon::now()->addDays(10)->toDateString(),
            ]);
        factory(Vehicle::class, $rentVehiclesCount)
            ->create([
                'location_id'    => $location->id,
                'rent_starts_at' => Carbon::now()->subDay()->toDateString(),
                'rent_ends_at'   => Carbon::now()->addDay()->toDateString(),
            ]);
        factory(Vehicle::class, $vehiclesCount)
            ->create([
                'location_id' => $location->id,
            ]);

        $url = action('Operations\VehiclesController@listLocationVehicles', [
            'location_id' => $location->id,
            'date'        => Carbon::now()->toDateString(),
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonCount($vehiclesCount + $rentVehiclesCount, 'data');
    }

    public function testAddVehicleSuccess()
    {
        $location          = factory(Location::class)->create();
        $vehicleStatusType = factory(VehicleStatusType::class)->create([
            'is_default' => true,
        ]);
        $request           = [
            'location_id'    => $location->id,
            'type'           => $this->faker->word,
            'make'           => $this->faker->word,
            'model'          => $this->faker->word,
            'registration'   => $this->faker->word,
            'rent_starts_at' => Carbon::now()->format('Y-m-d\TH:i:s\Z'),
            'rent_ends_at'   => Carbon::now()->addDay()->format('Y-m-d\TH:i:s\Z'),
        ];

        $url  = action('Operations\VehiclesController@store');
        $data = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->getData();

        $reloaded = Vehicle::findOrFail($data['id']);
        self::assertEquals($vehicleStatusType->id, $reloaded->latestStatus->type->id);
        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteVehicleSuccess()
    {
        $vehicle = factory(Vehicle::class)->create();

        $url = action('Operations\VehiclesController@destroy', [
            'vehicle_id' => $vehicle->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = Vehicle::find($vehicle->id);
        self::assertNull($reloaded);
    }

    public function testChangeVehicleStatusSuccess()
    {
        $vehicleStatus = factory(VehicleStatusType::class)->create();
        $vehicle       = factory(Vehicle::class)->create();

        $url = action('Operations\VehiclesController@changeStatus', [
            'vehicle_id'     => $vehicle->id,
            'status_type_id' => $vehicleStatus->id,
        ]);

        $this->patchJson($url)->assertStatus(200);

        $reloaded = Vehicle::findOrFail($vehicle->id);
        self::assertEquals($vehicleStatus->id, $reloaded->latestStatus->type->id);
    }
}
