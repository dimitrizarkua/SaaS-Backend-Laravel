<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateAssessmentReportSectionRoomRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name", "underlay_required", "trims_required", "restorable"},
 *     @OA\Property(
 *         property="name",
 *         description="AR section room text",
 *         type="string",
 *         example="Section cost item name",
 *     ),
 *     @OA\Property(
 *         property="flooring_type_id",
 *         description="Identifier of flooring type",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="flooring_subtype_id",
 *         description="Identifier of flooring subtype",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="dimensions_length",
 *         description="Dimensions length",
 *         type="number",
 *         format="float",
 *         example=1.72,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="dimensions_width",
 *         description="Dimensions width",
 *         type="number",
 *         format="float",
 *         example=0.95,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="dimensions_height",
 *         description="Dimensions height",
 *         type="number",
 *         format="float",
 *         example=2.50,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="dimensions_affected_length",
 *         description="Dimensions affected length",
 *         type="number",
 *         format="float",
 *         example=1.1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="dimensions_affected_width",
 *         description="Dimensions affected width",
 *         type="number",
 *         format="float",
 *         example=0.5,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="underlay_required",
 *         description="Indicates whether underlay is required or not",
 *         type="boolean",
 *         default=false,
 *         example=true,
 *     ),
 *     @OA\Property(
 *         property="underlay_type_id",
 *         description="Identifier of underaly type",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="underlay_type_note",
 *         description="Note for underlay type",
 *         type="string",
 *         example="Wooden or cement underlay",
 *     ),
 *     @OA\Property(
 *         property="dimensions_underlay_length",
 *         description="Dimensions underlay length",
 *         type="number",
 *         format="float",
 *         example=2.02,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="dimensions_underlay_width",
 *         description="Dimensions underlay width",
 *         type="number",
 *         format="float",
 *         example=3.14,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="trims_required",
 *         description="Indicates whether trim is required or not",
 *         type="boolean",
 *         default=false,
 *         example=true,
 *     ),
 *     @OA\Property(
 *         property="trim_type",
 *         description="Trim type",
 *         type="string",
 *         example="Choke trim",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="restorable",
 *         description="Indicates whether room is retorable or not",
 *         type="boolean",
 *         default=false,
 *         example=true,
 *     ),
 *     @OA\Property(
 *         property="non_restorable_reason_id",
 *         description="Identifier of non restorable reason",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="carpet_type_id",
 *         description="Identifier of carpet type",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="carpet_construction_type_id",
 *         description="Identifier of carpet construction type",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="carpet_age_id",
 *         description="Identifier of carpet age",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="carpet_face_fibre_id",
 *         description="Identifier of carpet face fibre",
 *         type="integer",
 *         example=1,
 *         nullable=true,
 *     ),
 * )
 */
class CreateAssessmentReportSectionRoomRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                        => 'required|string',
            'flooring_type_id'            => 'nullable|integer|exists:flooring_types,id',
            'flooring_subtype_id'         => 'nullable|integer|exists:flooring_subtypes,id',
            'dimensions_length'           => 'nullable|numeric',
            'dimensions_width'            => 'nullable|numeric',
            'dimensions_height'           => 'nullable|numeric',
            'dimensions_affected_length'  => 'nullable|numeric',
            'dimensions_affected_width'   => 'nullable|numeric',
            'underlay_required'           => 'required|boolean',
            'underlay_type_id'            => 'nullable|integer|exists:underlay_types,id',
            'underlay_type_note'          => 'nullable|string',
            'dimensions_underlay_length'  => 'nullable|numeric',
            'dimensions_underlay_width'   => 'nullable|numeric',
            'trims_required'              => 'required|boolean',
            'trim_type'                   => 'nullable|string',
            'restorable'                  => 'required|boolean',
            'non_restorable_reason_id'    => 'nullable|integer|exists:non_restorable_reasons,id',
            'carpet_type_id'              => 'nullable|integer|exists:carpet_types,id',
            'carpet_construction_type_id' => 'nullable|integer|exists:carpet_construction_types,id',
            'carpet_age_id'               => 'nullable|integer|exists:carpet_ages,id',
            'carpet_face_fibre_id'        => 'nullable|integer|exists:carpet_face_fibres,id',
        ];
    }
}
