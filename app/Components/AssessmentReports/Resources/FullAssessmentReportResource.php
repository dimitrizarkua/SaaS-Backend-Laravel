<?php

namespace App\Components\AssessmentReports\Resources;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\Jobs\Resources\FullJobResource;
use App\Components\Users\Resources\UserProfileMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FullAssessmentReportResource
 *
 * @package App\Components\AssessmentReports\Resources
 * @mixin \App\Components\AssessmentReports\Models\AssessmentReport
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/AssessmentReport")},
 * )
 */
class FullAssessmentReportResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="sections",
     *     type="array",
     *     @OA\Items(
     *         type="object",
     *         @OA\Property(
     *             property="items",
     *             type="array",
     *             @OA\Items(oneOf={
     *                 @OA\Schema(ref="#/components/schemas/AssessmentReportSectionTextBlock"),
     *                 @OA\Schema(ref="#/components/schemas/AssessmentReportSectionImage"),
     *                 @OA\Schema(ref="#/components/schemas/AssessmentReportSectionPhoto"),
     *                 @OA\Schema(ref="#/components/schemas/AssessmentReportSectionCostItem"),
     *                 @OA\Schema(ref="#/components/schemas/AssessmentReportSectionRoom"),
     *             })
     *         ),
     *         allOf={@OA\Schema(ref="#/components/schemas/AssessmentReportSection"),}
     *     ),
     * ),
     * @OA\Property(
     *     property="user",
     *     type="object",
     *     ref="#/components/schemas/UserProfileMiniResource"),
     * )
     * @OA\Property(
     *     property="job",
     *     type="object",
     *     ref="#/components/schemas/FullJobResource"),
     * )
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result         = parent::toArray($request);
        $result['user'] = UserProfileMiniResource::make($this->user);
        $result['job']  = FullJobResource::make($this->job);

        foreach ($result['sections'] as &$section) {
            if (in_array($section['type'], AssessmentReportSectionTypes::$textSectionTypes)) {
                $section['items'] = $section['text_blocks'];
            } elseif (AssessmentReportSectionTypes::IMAGE === $section['type']) {
                $section['items'] = $section['image'];
            } elseif (AssessmentReportSectionTypes::PHOTOS === $section['type']) {
                $section['items'] = $section['photos'];
            } elseif (AssessmentReportSectionTypes::COSTS === $section['type']) {
                $section['items'] = $section['cost_items'];
            } elseif (AssessmentReportSectionTypes::ROOM === $section['type']) {
                $section['items'] = $section['room'];
            }

            unset(
                $section['text_blocks'],
                $section['image'],
                $section['photos'],
                $section['cost_items'],
                $section['room']
            );
        }

        return $result;
    }
}
