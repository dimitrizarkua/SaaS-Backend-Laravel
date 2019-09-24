<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReportSectionTextBlock;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionTextBlockResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AssessmentReportSectionTextBlocksControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportSectionTextBlocksControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.manage',
    ];

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionTextBlock()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes)
        );
        $request = [
            'position' => $this->faker->numberBetween(1, 100),
            'text'     => $this->faker->text(),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionTextBlocksController@store', [
            'assessment_report_id' => $section->assessment_report_id,
            'section_id'           => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportSectionTextBlockResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReportSectionTextBlock::findOrFail($data['id']);

        self::assertEquals($section->id, $reloaded->assessment_report_section_id);
        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionTextBlock()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes)
        );
        /** @var AssessmentReportSectionTextBlock $model */
        $model   = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $request = [
            'position' => $this->faker->numberBetween(1, 100),
            'text'     => $this->faker->text(),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionTextBlocksController@update', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'text_block_id'        => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportSectionTextBlock::findOrFail($model->id);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionTextBlock()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes)
        );
        /** @var AssessmentReportSectionTextBlock $model */
        $model = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $url   = action('AssessmentReports\AssessmentReportSectionTextBlocksController@destroy', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
            'text_block_id'        => $model->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionTextBlock::findOrFail($model->id);
    }
}
