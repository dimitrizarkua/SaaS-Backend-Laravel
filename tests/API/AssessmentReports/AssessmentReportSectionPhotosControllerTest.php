<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto;
use App\Components\Photos\Models\Photo;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionPhotoResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AssessmentReportSectionPhotosControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportSectionPhotosControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.manage',
    ];

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionPhoto()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        $request = [
            'photo_id' => $photo->id,
            'position' => $this->faker->randomNumber(1),
            'caption'  => $this->faker->text(),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionPhotosController@store', [
            'assessment_report_id' => $section->assessment_report_id,
            'section_id'           => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportSectionPhotoResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReportSectionPhoto::findOrFail($data['id']);

        self::assertEquals($section->id, $reloaded->assessment_report_section_id);
        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionPhoto()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        /** @var AssessmentReportSectionPhoto $model */
        $model   = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $request = [
            'photo_id' => $photo->id,
            'position' => $this->faker->randomNumber(1),
            'caption'  => $this->faker->text(),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionPhotosController@update', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'photo_id'             => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportSectionPhoto::findOrFail($model->id);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionPhoto()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        /** @var AssessmentReportSectionPhoto $model */
        $model = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $url   = action('AssessmentReports\AssessmentReportSectionPhotosController@destroy', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'photo_id'             => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionPhoto::findOrFail($model->id);
    }
}
