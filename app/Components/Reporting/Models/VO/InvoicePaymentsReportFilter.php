<?php

namespace App\Components\Reporting\Models\VO;

use App\Components\Finance\Models\InvoiceItem;
use App\Components\Locations\Models\LocationUser;
use App\Models\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class InvoicePaymentsReportFilter
 *
 * @package App\Components\Reporting\Models\VO
 */
class InvoicePaymentsReportFilter extends Filter
{
    /**
     * @var null|int
     */
    private $location_id;

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var null|int
     */
    public $recipient_contact_id;

    /**
     * @var null|string
     */
    public $type;

    /**
     * @var null|float
     */
    public $amount_from;

    /**
     * @var null|float
     */
    public $amount_to;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $date_from;

    /**
     * @var \Illuminate\Support\Carbon|null
     */
    private $date_to;

    /**
     * @param string|null $dateFrom
     *
     * @return \App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter
     */
    public function setDateFrom(?string $dateFrom): self
    {
        if (null !== $dateFrom) {
            $this->date_from = new Carbon($dateFrom);
        }

        return $this;
    }

    /**
     * @param string|null $dateTo
     *
     * @return \App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter
     */
    public function setDateTo(?string $dateTo): self
    {
        if (null !== $dateTo) {
            $this->date_to = new Carbon($dateTo);
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDateFrom(): ?\Illuminate\Support\Carbon
    {
        if (null === $this->date_from) {
            return now()->startOfMonth();
        }

        return $this->date_from;
    }

    /**
     * @return \Illuminate\Support\Carbon|null
     */
    public function getDateTo(): ?\Illuminate\Support\Carbon
    {
        if (null === $this->date_to) {
            return now()->endOfMonth();
        }

        return $this->date_to;
    }

    /**
     * @param int|null $locationId
     *
     * @return \App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter
     */
    public function setLocationId(?int $locationId): self
    {
        $this->location_id = $locationId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLocationId(): ?int
    {
        if (null === $this->location_id) {
            if (null === $this->user_id) {
                return null;
            }

            /** @var LocationUser $primaryLocation */
            $primaryLocation = LocationUser::query()
                ->where([
                    'user_id' => $this->user_id,
                    'primary' => true,
                ])
                ->first();

            return null !== $primaryLocation ? $primaryLocation->location_id : null;
        }

        return $this->location_id;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->type, function (Builder $query) {
                $query->whereHas('payments', function (Builder $query) {
                    $query->where('type', $this->type);
                });
            })
            ->when($this->recipient_contact_id, function (Builder $query) {
                $query->where('recipient_contact_id', $this->recipient_contact_id);
            })
            ->when($this->amount_from, function (Builder $query) {
                $this->buildInvoicePaymentAmountQuery($query, $this->amount_from, '>=');
            })
            ->when($this->amount_to, function (Builder $query) {
                $this->buildInvoicePaymentAmountQuery($query, $this->amount_to, '<=');
            });
    }

    /**
     * @param Builder $query    Query builder.
     * @param float   $amount   Amount from or amount to value.
     * @param string  $operator Compare operator.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildInvoicePaymentAmountQuery(Builder $query, float $amount, string $operator): Builder
    {
        $totalAmountQuery = '(' . InvoiceItem::withTotalAmountExcludeTax('total_amount')->toSql() . ')';

        return $query->where(function (Builder $query) use ($operator, $amount, $totalAmountQuery) {
            $query->whereHas('payments', function (Builder $query) use ($operator, $amount) {
                $query->where('invoice_payment.amount', $operator, $amount);
            })
                ->orWhereRaw(sprintf('(%s) %s (%d)', $totalAmountQuery, $operator, $amount));
        });
    }
}
