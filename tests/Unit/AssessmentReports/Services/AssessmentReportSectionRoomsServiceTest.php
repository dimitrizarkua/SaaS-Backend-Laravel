<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReportSectionRoom;
use App\Components\AssessmentReports\Models\CarpetAge;
use App\Components\AssessmentReports\Models\CarpetConstructionType;
use App\Components\AssessmentReports\Models\CarpetFaceFibre;
use App\Components\AssessmentReports\Models\CarpetType;
use App\Components\AssessmentReports\Models\FlooringSubtype;
use App\Components\AssessmentReports\Models\FlooringType;
use App\Components\AssessmentReports\Models\NonRestorableReason;
use App\Components\AssessmentReports\Models\UnderlayType;
use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionRoomData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionRoomsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportSectionRoomsServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportSectionRoomsServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportSectionRoomsService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportSectionRoomsService::class);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionRoom()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::ROOM);
        $data    = new AssessmentReportSectionRoomData([
            'name'                        => $this->faker->words(3, true),
            'flooring_type_id'            => factory(FlooringType::class)->create()->id,
            'flooring_subtype_id'         => factory(FlooringSubtype::class)->create()->id,
            'dimensions_length'           => $this->faker->randomFloat(2, 1, 500),
            'dimensions_width'            => $this->faker->randomFloat(2, 1, 500),
            'dimensions_height'           => $this->faker->randomFloat(2, 1, 500),
            'dimensions_affected_length'  => $this->faker->randomFloat(2, 1, 500),
            'dimensions_affected_width'   => $this->faker->randomFloat(2, 1, 500),
            'underlay_required'           => true,
            'underlay_type_id'            => factory(UnderlayType::class)->create()->id,
            'underlay_type_note'          => $this->faker->text(),
            'dimensions_underlay_length'  => $this->faker->randomFloat(2, 1, 500),
            'dimensions_underlay_width'   => $this->faker->randomFloat(2, 1, 500),
            'trims_required'              => true,
            'trim_type'                   => $this->faker->words(2, true),
            'restorable'                  => true,
            'non_restorable_reason_id'    => factory(NonRestorableReason::class)->create()->id,
            'carpet_type_id'              => factory(CarpetType::class)->create()->id,
            'carpet_construction_type_id' => factory(CarpetConstructionType::class)->create()->id,
            'carpet_age_id'               => factory(CarpetAge::class)->create()->id,
            'carpet_face_fibre_id'        => factory(CarpetFaceFibre::class)->create()->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $room = $this->service->create($data, $section->assessment_report_id, $section->id);

        self::assertEquals($section->id, $room->assessment_report_section_id);
        self::compareDataWithModel($data->toArray(), $room);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionRoomWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::ROOM,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        $data    = new AssessmentReportSectionRoomData([
            'name'              => $this->faker->words(3, true),
            'underlay_required' => false,
            'trims_required'    => false,
            'restorable'        => false,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionRoomWhenSectionIsNonRoom()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::TEXT);
        $data    = new AssessmentReportSectionRoomData([
            'name'              => $this->faker->words(3, true),
            'underlay_required' => false,
            'trims_required'    => false,
            'restorable'        => false,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionRoom()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::ROOM);
        /** @var AssessmentReportSectionRoom $room */
        $room = factory(AssessmentReportSectionRoom::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data = new AssessmentReportSectionRoomData([
            'name'                        => $this->faker->words(3, true),
            'flooring_type_id'            => factory(FlooringType::class)->create()->id,
            'flooring_subtype_id'         => factory(FlooringSubtype::class)->create()->id,
            'dimensions_length'           => $this->faker->randomFloat(2, 1, 500),
            'dimensions_width'            => $this->faker->randomFloat(2, 1, 500),
            'dimensions_height'           => $this->faker->randomFloat(2, 1, 500),
            'dimensions_affected_length'  => $this->faker->randomFloat(2, 1, 500),
            'dimensions_affected_width'   => $this->faker->randomFloat(2, 1, 500),
            'underlay_required'           => true,
            'underlay_type_id'            => factory(UnderlayType::class)->create()->id,
            'underlay_type_note'          => $this->faker->text(),
            'dimensions_underlay_length'  => $this->faker->randomFloat(2, 1, 500),
            'dimensions_underlay_width'   => $this->faker->randomFloat(2, 1, 500),
            'trims_required'              => true,
            'trim_type'                   => $this->faker->words(2, true),
            'restorable'                  => true,
            'non_restorable_reason_id'    => factory(NonRestorableReason::class)->create()->id,
            'carpet_type_id'              => factory(CarpetType::class)->create()->id,
            'carpet_construction_type_id' => factory(CarpetConstructionType::class)->create()->id,
            'carpet_age_id'               => factory(CarpetAge::class)->create()->id,
            'carpet_face_fibre_id'        => factory(CarpetFaceFibre::class)->create()->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->update(
            $data,
            $room->section->assessment_report_id,
            $room->section->id,
            $room->id
        );

        $reloaded = AssessmentReportSectionRoom::findOrFail($room->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportSectionRoomWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::ROOM,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionRoom $room */
        $room = factory(AssessmentReportSectionRoom::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data = new AssessmentReportSectionRoomData([
            'name'              => $this->faker->words(3, true),
            'underlay_required' => false,
            'trims_required'    => false,
            'restorable'        => false,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update(
            $data,
            $room->section->assessment_report_id,
            $room->section->id,
            $room->id
        );
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionRoom()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::ROOM);
        /** @var AssessmentReportSectionRoom $room */
        $room = factory(AssessmentReportSectionRoom::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->delete(
            $room->section->assessment_report_id,
            $room->section->id,
            $room->id
        );

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionRoom::findOrFail($room->id);
    }

    /**
     * @throws \Exception
     */
    public function testFailToDeleteAssessmentReportSectionRoomWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::ROOM,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionRoom $room */
        $room = factory(AssessmentReportSectionRoom::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete(
            $room->section->assessment_report_id,
            $room->section->id,
            $room->id
        );
    }
}
