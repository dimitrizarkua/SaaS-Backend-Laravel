<?php

namespace App\Http\Requests\Finance;

use App\Components\Locations\Models\LocationUser;
use App\Components\Reporting\Models\VO\GLAccountTrialReportFilterData;
use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class FilterGLAccountTrialReportRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="location_id",
 *          description="Identifier of location",
 *          type="integer",
 *          example="1"
 *     ),
 *     @OA\Property(
 *          property="date_to",
 *          description="Defines end date for filtering transactions by account.",
 *          type="string",
 *          format="date",
 *          example="2019-02-02"
 *     )
 * )
 */
class FilterGLAccountTrialReportRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'location_id' => 'required|integer|exists:locations,id',
            'date_to'     => 'string|date_format:Y-m-d',
        ];
    }

    /**
     * @return \App\Components\Reporting\Models\VO\GLAccountTrialReportFilterData
     *
     * @throws \JsonMapper_Exception
     */
    public function getGLAccountTrialReportFilterData(): GLAccountTrialReportFilterData
    {
        $filter = new GLAccountTrialReportFilterData($this->validated());

        return $filter;
    }
}
