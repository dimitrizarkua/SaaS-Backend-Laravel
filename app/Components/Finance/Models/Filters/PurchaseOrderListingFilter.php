<?php

namespace App\Components\Finance\Models\Filters;

use App\Components\Locations\Models\LocationUser;
use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class PurchaseOrderListingFilter
 *
 * @package App\Components\Finance\Models\VO
 */
class PurchaseOrderListingFilter extends Filter
{
    /**
     * @var array
     */
    private $locations;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var null|int
     */
    public $recipient_contact_id;

    /**
     * @var null|int
     */
    public $job_id;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $date_from;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $date_to;

    /**
     * @param string|null $date_from
     *
     * @return \App\Components\Finance\Models\Filters\PurchaseOrderListingFilter
     */
    public function setDateFrom(?string $date_from): self
    {
        if (null !== $date_from) {
            $this->date_from = new Carbon($date_from);
        }

        return $this;
    }

    /**
     * @param string|null $date_to
     *
     * @return \App\Components\Finance\Models\Filters\PurchaseOrderListingFilter
     */
    public function setDateTo(?string $date_to): self
    {
        if (null !== $date_to) {
            $this->date_to = new Carbon($date_to);
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDateFrom(): ?\Illuminate\Support\Carbon
    {
        return $this->date_from;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDateTo(): ?\Illuminate\Support\Carbon
    {
        return $this->date_to;
    }

    /**
     * @param array $locations
     */
    public function setLocations(array $locations): void
    {
        $this->locations = $locations;
    }

    /**
     * @return array
     */
    public function getLocations(): array
    {
        if (null === $this->locations) {
            if (null === $this->user_id) {
                throw new \RuntimeException('One of fields: user_id OR locations must be set');
            }

            return LocationUser::query()
                ->where('user_id', $this->user_id)
                ->get()
                ->pluck('location_id')
                ->toArray();
        }

        return $this->locations;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $query): Builder
    {
        return $query->whereIn('location_id', $this->getLocations())
            ->when($this->recipient_contact_id, function (Builder $query) {
                return $query->where('recipient_contact_id', $this->recipient_contact_id);
            })
            ->when($this->job_id, function (Builder $query) {
                return $query->where('job_id', $this->job_id);
            })
            ->when($this->getDateFrom(), function (Builder $query) {
                return $query->whereDate('date', '>=', $this->getDateFrom());
            })
            ->when($this->getDateTo(), function (Builder $query) {
                return $query->whereDate('date', '<=', $this->getDateTo());
            });
    }
}
