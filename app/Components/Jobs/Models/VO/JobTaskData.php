<?php

namespace App\Components\Jobs\Models\VO;

use App\Core\JsonModel;
use Illuminate\Support\Carbon;

/**
 * Class JobTaskData
 *
 * @package App\Components\Jobs\Models\VO
 */
class JobTaskData extends JsonModel
{
    /** @var int $job_task_type_id */
    public $job_task_type_id;

    /** @var string|null $name */
    public $name;

    /** @var string|null $internal_note */
    public $internal_note;

    /** @var string|null $scheduling_note */
    public $scheduling_note;

    /** @var string|null $kpi_missed_reason */
    public $kpi_missed_reason;

    /** @var \Illuminate\Support\Carbon|null $due_at */
    public $due_at;

    /** @var \Illuminate\Support\Carbon|null $starts_at */
    public $starts_at;

    /** @var \Illuminate\Support\Carbon $ends_at */
    public $ends_at;

    /** @var \Illuminate\Support\Carbon|null $kpi_missed_at */
    public $kpi_missed_at;

    /**
     * @param string|null $dueAt
     */
    public function setDueAt(?string $dueAt): void
    {
        $this->due_at = $dueAt ? Carbon::make($dueAt) : null;
    }

    /**
     * @param string $startsAt
     */
    public function setStartsAt(string $startsAt): void
    {
        $this->starts_at = Carbon::make($startsAt);
    }

    /**
     * @param string $endsAt
     */
    public function setEndsAt(string $endsAt): void
    {
        $this->ends_at = Carbon::make($endsAt);
    }
}
