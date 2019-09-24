<?php

namespace App\Components\Operations\Interfaces;

use App\Components\Operations\Models\Vehicle;
use App\Components\Operations\Models\VehicleStatus;
use App\Components\Operations\Models\VO\VehicleData;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface VehiclesServiceInterface
 *
 * @package App\Components\Operations\Interfaces
 */
interface VehiclesServiceInterface
{
    /**
     * Get vehicle by id.
     *
     * @param int $vehicleId Vehicle id.
     *
     * @return \App\Components\Operations\Models\Vehicle
     */
    public function getVehicle(int $vehicleId): Vehicle;

    /**
     * Get all vehicles assigned to the specified location.
     *
     * @param int                 $locationId Location id.
     * @param \Carbon\Carbon|null $date       Date for filtering.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listLocationVehicles(int $locationId, ?Carbon $date = null): Collection;

    /**
     * Create new vehicle.
     *
     * @param \App\Components\Operations\Models\VO\VehicleData $data   Vehicle data.
     * @param int                                              $userId User id.
     *
     * @return \App\Components\Operations\Models\Vehicle
     */
    public function createVehicle(VehicleData $data, int $userId): Vehicle;

    /**
     * Change status of the vehicle.
     *
     * @param int $vehicleId Vehicle id.
     * @param int $statusId  New status id.
     * @param int $userId    User id.
     *
     * @return \App\Components\Operations\Models\VehicleStatus
     */
    public function changeVehicleStatus(int $vehicleId, int $statusId, int $userId): VehicleStatus;

    /**
     * Delete vehicle.
     *
     * @param int $vehicleId Vehicle id.
     *
     * @return void
     */
    public function deleteVehicle(int $vehicleId): void;
}
