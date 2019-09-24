<?php

namespace App\Components\Reporting\Models\Filters;

use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class IncomeReportFilter
 * Filter for Finance: Report - Income by Account Summary.
 *
 * @package App\Components\Reporting\Models\Filters
 */
class IncomeReportFilter extends Filter
{
    /**
     * @var integer
     */
    public $location_id;

    /**
     * @var null|int
     */
    public $gl_account_id;

    /**
     * @var null|int
     */
    public $recipient_contact_id;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $date_from;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    public $date_to;

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDateFrom(): ?Carbon
    {
        return $this->date_from;
    }

    /**
     * @param string|null $dateFrom
     *
     * @return self
     */
    public function setDateFrom(?string $dateFrom): self
    {
        if (null !== $dateFrom) {
            $this->date_from = new Carbon($dateFrom);
        } else {
            $this->date_from = null;
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDateTo(): ?Carbon
    {
        return $this->date_to;
    }

    /**
     * @param string|null $dateTo
     *
     * @return self
     */
    public function setDateTo(?string $dateTo): self
    {
        if (null !== $dateTo) {
            $this->date_to = new Carbon($dateTo);
        } else {
            $this->date_to = null;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGLAccountId(): ?int
    {
        return $this->gl_account_id;
    }

    /**
     * @param int|null $glAccountId
     *
     * @return self
     */
    public function setGLAccountId(?int $glAccountId): self
    {
        $this->gl_account_id = $glAccountId;

        return $this;
    }

    /**
     * @param int $locationId
     *
     * @return self
     */
    public function setLocationId(int $locationId): self
    {
        $this->location_id = $locationId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLocationId(): ?int
    {
        return $this->location_id;
    }

    /**
     * @param int|null $recipientContactId
     *
     * @return self
     */
    public function setRecipientContactId(?int $recipientContactId): self
    {
        $this->recipient_contact_id = $recipientContactId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecipientContactId(): ?int
    {
        return $this->recipient_contact_id;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->getLocationId(),
                function (Builder $query) {
                    return $query->where('location_id', '=', $this->getLocationId());
                }
            )
            ->when(
                $this->getRecipientContactId(),
                function (Builder $query) {
                    return $query->where('recipient_contact_id', '=', $this->getRecipientContactId());
                }
            )
            ->when(
                $this->getDateFrom(),
                function (Builder $query) {
                    return $query->whereDate('date', '>=', $this->getDateFrom());
                },
                function (Builder $query) {
                    return $query->whereDate('date', '>=', new Carbon('first day of this month'));
                }
            )
            ->when(
                $this->getDateTo(),
                function (Builder $query) {
                    return $query->whereDate('date', '<=', $this->getDateTo());
                },
                function (Builder $query) {
                    return $query->whereDate('date', '<=', new Carbon('last day of this month'));
                }
            );
    }
}
