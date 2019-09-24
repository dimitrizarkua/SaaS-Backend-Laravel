<?php

namespace App\Components\Operations\Services;

use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Components\Operations\Exceptions\NotAllowedException;
use App\Components\Operations\Interfaces\VehiclesServiceInterface;
use App\Components\Operations\Models\Vehicle;
use App\Components\Operations\Models\VehicleStatus;
use App\Components\Operations\Models\VehicleStatusType;
use App\Components\Operations\Models\VO\VehicleData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class VehiclesService
 *
 * @package App\Components\Operations\Services
 */
class VehiclesService implements VehiclesServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getVehicle(int $vehicleId): Vehicle
    {
        return Vehicle::findOrFail($vehicleId);
    }

    /**
     * {@inheritdoc}
     */
    public function listLocationVehicles(int $locationId, ?Carbon $date = null): Collection
    {
        /* @var \App\Components\Locations\Models\Location $location */
        $location = app()->make(LocationsServiceInterface::class)->getLocation($locationId);

        return $location
            ->vehicles()
            ->with('latestStatus.type')
            ->when(null !== $date, function (Builder $query) use ($date) {
                $query->where(function (Builder $query) {
                    $query->whereNull('rent_starts_at')
                        ->whereNull('rent_ends_at');
                })->orWhere(function (Builder $query) use ($date) {
                    $query->whereDate('rent_starts_at', '<=', $date)
                        ->whereDate('rent_ends_at', '>=', $date);
                });
            })
            ->get()
            ->when(
                null !== $date,
                function (Collection $vehicles) use ($date, $locationId) {
                    $booked = DB::query()
                        ->select('vehicles.id')
                        ->from('vehicles')
                        ->leftJoin('job_run_vehicle_assignments', 'vehicles.id', '=', 'vehicle_id')
                        ->leftJoin('job_runs', 'job_run_id', '=', 'job_runs.id')
                        ->whereDate('date', $date->toDateString())
                        ->where('vehicles.location_id', $locationId)
                        ->pluck('id')
                        ->toArray();

                    return $vehicles->each(function (Vehicle $vehicle) use ($booked) {
                        $vehicle->is_booked = in_array($vehicle->id, $booked, true);
                    });
                }
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createVehicle(VehicleData $data, int $userId): Vehicle
    {
        return DB::transaction(function () use ($data, $userId) {
            $vehicle = Vehicle::create($data->toArray());
            $status  = VehicleStatusType::getDefaultStatus();
            $vehicle->changeStatus($status, $userId);

            return $vehicle;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function changeVehicleStatus(int $vehicleId, int $statusId, int $userId): VehicleStatus
    {
        $vehicle = $this->getVehicle($vehicleId);
        $status  = VehicleStatusType::findOrFail($statusId);

        return $vehicle->changeStatus($status, $userId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function deleteVehicle(int $vehicleId): void
    {
        $vehicle = $this->getVehicle($vehicleId);

        try {
            $vehicle->delete();
        } catch (\Exception $e) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }
    }
}
