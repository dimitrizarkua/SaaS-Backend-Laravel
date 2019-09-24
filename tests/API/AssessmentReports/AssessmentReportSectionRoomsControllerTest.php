<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReportSectionRoom;
use App\Components\AssessmentReports\Models\CarpetAge;
use App\Components\AssessmentReports\Models\CarpetConstructionType;
use App\Components\AssessmentReports\Models\CarpetFaceFibre;
use App\Components\AssessmentReports\Models\CarpetType;
use App\Components\AssessmentReports\Models\FlooringSubtype;
use App\Components\AssessmentReports\Models\FlooringType;
use App\Components\AssessmentReports\Models\NonRestorableReason;
use App\Components\AssessmentReports\Models\UnderlayType;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionRoomResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AssessmentReportSectionRoomsControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportSectionRoomsControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.manage',
    ];

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionRoom()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::ROOM);
        $request = [
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
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionRoomsController@store', [
            'assessment_report_id' => $section->assessment_report_id,
            'section_id'           => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportSectionRoomResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReportSectionRoom::findOrFail($data['id']);

        self::assertEquals($section->id, $reloaded->assessment_report_section_id);
        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionRoom()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::ROOM);
        /** @var AssessmentReportSectionRoom $model */
        $model   = factory(AssessmentReportSectionRoom::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $request = [
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
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionRoomsController@update', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'room_id'              => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportSectionRoom::findOrFail($model->id);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionRoom()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::ROOM);
        /** @var AssessmentReportSectionRoom $model */
        $model = factory(AssessmentReportSectionRoom::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $url   = action('AssessmentReports\AssessmentReportSectionRoomsController@destroy', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'room_id'              => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionRoom::findOrFail($model->id);
    }
}
