<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Events\AccountingOrganizationCreated;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\VO\CreateAccountingOrganizationData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class AccountingOrganizationsService
 *
 * @package App\Components\Finance\Services
 */
class AccountingOrganizationsService implements AccountingOrganizationsServiceInterface
{
    /**
     * @inheritdoc
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getAccountingOrganization(int $accountId): AccountingOrganization
    {
        return AccountingOrganization::findOrFail($accountId);
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    public function create(CreateAccountingOrganizationData $data): AccountingOrganization
    {
        $organization = DB::transaction(function () use ($data) {
            $modelData              = $data->toArray();
            $modelData['is_active'] = true;

            $model = new AccountingOrganization($modelData);
            $model->saveOrFail();

            $locationId = $data->getLocationId();
            if ($locationId) {
                $this->addLocation($model->id, $data->getLocationId());
            }

            return $model;
        });

        event(new AccountingOrganizationCreated($organization));

        return $organization;
    }

    /**
     * @inheritdoc
     *
     * @throws NotAllowedException
     */
    public function findActiveAccountOrganizationByLocation(int $locationId): ?AccountingOrganization
    {
        return AccountingOrganization::query()
            ->whereHas('locations', function (Builder $query) use ($locationId) {
                return $query->where('id', $locationId);
            })
            ->where('is_active', true)
            ->first();
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     * @throws NotAllowedException
     */
    public function addLocation(int $accountId, int $locationId): void
    {
        try {
            $this->getAccountingOrganization($accountId)
                ->locations()
                ->attach($locationId);
        } catch (\Exception $e) {
            $message = 'This location has been already linked to this account organization.';
            throw new NotAllowedException($message);
        }
    }

    /**
     * @inheritdoc
     */
    public function removeLocation(int $accountId, int $locationId): void
    {
        $this->getAccountingOrganization($accountId)
            ->locations()
            ->detach($locationId);
    }
}
