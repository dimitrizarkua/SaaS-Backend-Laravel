<?php

namespace App\Components\Reporting\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountTrialReportResource
 *
 * @package App\Components\Reporting\Resources
 * @OA\Schema(
 *     type="object",
 * )
 */
class GLAccountTrialReportResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="assets",
     *      type="array",
     *      @OA\Items(
     *         ref="#/components/schemas/GLAccountTrialReportResourceItem"
     *     )
     * )
     * @OA\Property(
     *      property="revenues",
     *      type="array",
     *      @OA\Items(
     *         ref="#/components/schemas/GLAccountTrialReportResourceItem"
     *     )
     * )
     * @OA\Property(
     *      property="liabilities",
     *      type="array",
     *      @OA\Items(
     *         ref="#/components/schemas/GLAccountTrialReportResourceItem"
     *     )
     * )
     *  @OA\Property(
     *      property="totals",
     *      type="array",
     *      @OA\Items(
     *         ref="#/components/schemas/GLAccountTrialReportResourceItem"
     *     )
     * )
     */
}
