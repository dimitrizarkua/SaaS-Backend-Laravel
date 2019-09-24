<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReportSectionImage;
use App\Components\Photos\Models\Photo;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionImageResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AssessmentReportSectionImagesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportSectionImagesControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.manage',
    ];

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionImage()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::IMAGE);
        $request = [
            'photo_id'      => $photo->id,
            'caption'       => $this->faker->text(),
            'desired_width' => $this->faker->numberBetween(256, 4096),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionImagesController@store', [
            'assessment_report_id' => $section->assessment_report_id,
            'section_id'           => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportSectionImageResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReportSectionImage::findOrFail($data['id']);

        self::assertEquals($section->id, $reloaded->assessment_report_section_id);
        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionImage()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::IMAGE);
        /** @var AssessmentReportSectionImage $model */
        $model   = factory(AssessmentReportSectionImage::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $request = [
            'photo_id'      => $photo->id,
            'caption'       => $this->faker->text(),
            'desired_width' => $this->faker->numberBetween(256, 4096),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionImagesController@update', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'image_id'             => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportSectionImage::findOrFail($model->id);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionImage()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::IMAGE);
        /** @var AssessmentReportSectionImage $model */
        $model = factory(AssessmentReportSectionImage::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $url   = action('AssessmentReports\AssessmentReportSectionImagesController@destroy', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'image_id'             => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionImage::findOrFail($model->id);
    }
}
