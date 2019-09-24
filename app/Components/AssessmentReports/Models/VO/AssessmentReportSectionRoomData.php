<?php

namespace App\Components\AssessmentReports\Models\VO;

/**
 * Class AssessmentReportSectionRoomData
 *
 * @package App\Components\AssessmentReports\Models\VO
 */
class AssessmentReportSectionRoomData extends AbstractAssessmentReportData
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int|null
     */
    public $flooring_type_id;

    /**
     * @var int|null
     */
    public $flooring_subtype_id;

    /**
     * @var float|null
     */
    public $dimensions_length;

    /**
     * @var float|null
     */
    public $dimensions_width;

    /**
     * @var float|null
     */
    public $dimensions_height;

    /**
     * @var float|null
     */
    public $dimensions_affected_length;

    /**
     * @var float|null
     */
    public $dimensions_affected_width;

    /**
     * @var boolean
     */
    public $underlay_required;

    /**
     * @var int|null
     */
    public $underlay_type_id;

    /**
     * @var string|null
     */
    public $underlay_type_note;

    /**
     * @var float|null
     */
    public $dimensions_underlay_length;

    /**
     * @var float|null
     */
    public $dimensions_underlay_width;

    /**
     * @var boolean
     */
    public $trims_required;

    /**
     * @var string|null
     */
    public $trim_type;

    /**
     * @var boolean
     */
    public $restorable;

    /**
     * @var int|null
     */
    public $non_restorable_reason_id;

    /**
     * @var int|null
     */
    public $carpet_type_id;

    /**
     * @var int|null
     */
    public $carpet_construction_type_id;

    /**
     * @var int|null
     */
    public $carpet_age_id;

    /**
     * @var int|null
     */
    public $carpet_face_fibre_id;
}
