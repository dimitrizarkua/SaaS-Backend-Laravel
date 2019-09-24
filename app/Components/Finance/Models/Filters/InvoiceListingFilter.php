<?php

namespace App\Components\Finance\Models\Filters;

use App\Components\Locations\Models\LocationUser;
use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class InvoiceListingFilter
 *
 * @package App\Components\Finance\Models\VO
 */
class InvoiceListingFilter extends Filter
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
    private $due_date_from;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $due_date_to;

    /**
     * @param string|null $due_date_from
     *
     * @return self
     */
    public function setDueDateFrom($due_date_from): self
    {
        if (null !== $due_date_from) {
            $this->due_date_from = new Carbon($due_date_from);
        }

        return $this;
    }

    /**
     * @param string|null $due_date_to
     *
     * @return self
     */
    public function setDueDateTo($due_date_to): self
    {
        if (null !== $due_date_to) {
            $this->due_date_to = new Carbon($due_date_to);
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDueDateFrom(): ?\Illuminate\Support\Carbon
    {
        return $this->due_date_from;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDueDateTo(): ?\Illuminate\Support\Carbon
    {
        return $this->due_date_to;
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

            return LocationUser::where('user_id', $this->user_id)
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
            ->when($this->job_id, function (Builder $query) {
                return $query->where('job_id', $this->job_id);
            })
            ->when($this->getDueDateFrom(), function (Builder $query) {
                return $query->whereDate('due_at', '>=', $this->getDueDateFrom());
            })
            ->when($this->getDueDateTo(), function (Builder $query) {
                return $query->whereDate('due_at', '<=', $this->getDueDateTo());
            })
            ->when($this->recipient_contact_id, function (Builder $query) {
                return $query->where('recipient_contact_id', $this->recipient_contact_id);
            });
    }
}
