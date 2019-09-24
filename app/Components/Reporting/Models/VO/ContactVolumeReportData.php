<?php

namespace App\Components\Reporting\Models\VO;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class ContactVolumeReportData
 *
 * @package App\Components\Reporting\Models\VO
 *
 * @OA\Schema(
 *     type="object",
 *     required={"managed", "new_leads", "touched", "converted", "meetings", "chart", "tags", "staff"}
 * )
 */
class ContactVolumeReportData implements Arrayable
{
    /**
     * @OA\Property(
     *     property="managed",
     *     description="Number of managed contacts with the period by location.",
     *     type="integer",
     *     example="10",
     *     minimum=0,
     * ),
     */

    /** @var int */
    public $managed = 0;

    /**
     * @OA\Property(
     *     property="new_leads",
     *     description="Number of any contact who during the period had their status set to lead where it previously
    was not lead.",
     *     type="integer",
     *     example="10",
     *     minimum=0
     *),
     */

    /** @var int */
    public $newLeads = 0;

    /**
     * @OA\Property(
     *     property="touched",
     *     description="Number of any notes, meetings and follow-ups that have been added for the managed contact
    within the period.",
     *     type="integer",
     *     example="10",
     *     minimum=0,
     * ),
     */

    /** @var int */
    public $touched = 0;

    /**
     * @OA\Property(
     *     property="converted",
     *     description="Number of leads who during the period had their status changed to active.",
     *     type="integer",
     *     example="10",
     *     minimum=0,
     *),
     */

    /** @var int */
    public $converted = 0;

    /**
     * @OA\Property(
     *     property="meetings",
     *     description="Number of meetings during the period.",
     *     type="integer",
     *     example="10",
     *     minimum=0,
     *),
     */

    /** @var int */
    public $meetings = 0;

    /**
     * @OA\Property(
     *     property="revenue",
     *     description="Revenue amount generated where the job-customer or job-referrer is a managed contact.
    Calculated where the job invoice date is within the given period",
     *     type="number",
     *     format="float",
     *     example="10.22",
     *     minimum=0,
     *),
     */

    /** @var float */
    public $revenue = 0;

    /**
     * @OA\Property(
     *     property="chart",
     *     type="array",
     *     @OA\Items(
     *         type="object",
     *         required={"x","y"},
     *         @OA\Property(
     *             property="x",
     *             description="Date",
     *             type="date",
     *             example="2019-02-01"
     *         ),
     *         @OA\Property(
     *             property="y",
     *             description="Revenue for a specified date.",
     *             type="number",
     *             format="float",
     *             example="10.44",
     *         ),
     *     ),
     * ),
     */

    /** @var array */
    public $chart = [];

    /**
     * @OA\Property(
     *     property="tags",
     *     type="array",
     *     @OA\Items(
     *         type="object",
     *         required={"name","count","percent"},
     *         @OA\Property(
     *             property="name",
     *             description="Tag name.",
     *             type="string",
     *         ),
     *         @OA\Property(
     *             property="count",
     *             description="Number of uses per period.",
     *             type="integer",
     *             example="23",
     *         ),
     *         @OA\Property(
     *             property="percent",
     *             description="Percentage used compared to all tags.",
     *             type="number",
     *             format="float",
     *             example="10.44",
     *         ),
     *     ),
     * ),
     */

    /** @var array */
    public $tags = [];

    /**
     * @OA\Property(
     *     property="staff",
     *     type="array",
     *     @OA\Items(
     *         type="object",
     *         required={"staff","leads","managed", "revenue"},
     *         @OA\Property(
     *             property="staff",
     *             description="User name",
     *             type="string",
     *             example="Bobby Tables"
     *         ),
     *         @OA\Property(
     *             property="leads",
     *             description="Number of leads per period.",
     *             type="integer",
     *             example="23",
     *         ),
     *         @OA\Property(
     *             property="managed",
     *             description="Number of managed accounts per period.",
     *             type="integer",
     *             example="23",
     *         ),
     *         @OA\Property(
     *             property="revenue",
     *             description="Revenue for specified staff person.",
     *             type="number",
     *             format="float",
     *             example="10.44",
     *         ),
     *     ),
     * ),
     */

    /** @var array */
    public $staff = [];

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'managed'   => $this->managed,
            'new_leads' => $this->newLeads,
            'touched'   => $this->touched,
            'converted' => $this->converted,
            'meetings'  => $this->meetings,
            'revenue'   => $this->revenue,
            'chart'     => $this->chart,
            'tags'      => $this->tags,
            'staff'     => $this->staff,
        ];
    }
}
