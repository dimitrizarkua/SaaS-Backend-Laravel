<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportHeadingStyles;
use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AssessmentReportSectionsControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportSectionsControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.view',
        'assessment_reports.manage',
    ];

    public function testGetAssessmentReportSections()
    {
        $count = $this->faker->numberBetween(1, 5);
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        factory(AssessmentReportSection::class, $count)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $url = action('AssessmentReports\AssessmentReportSectionsController@index', [
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetAssessmentReportSection()
    {
        /** @var AssessmentReportSection $model */
        $model = factory(AssessmentReportSection::class)->create();
        $url   = action('AssessmentReports\AssessmentReportSectionsController@show', [
            'assessment_report_id' => $model->assessment_report_id,
            'section_id'           => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportSectionResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportSection()
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $request          = [
            'type'          => $this->faker->randomElement(AssessmentReportSectionTypes::values()),
            'position'      => $this->faker->numberBetween(1, 100),
            'heading'       => $this->faker->text(),
            'heading_style' => $this->faker->randomElement(AssessmentReportHeadingStyles::values()),
            'heading_color' => $this->faker->numberBetween(0, 16777215),
            'text'          => $this->faker->text(),
        ];
        $url              = action('AssessmentReports\AssessmentReportSectionsController@store', [
            'assessment_report_id' => $assessmentReport->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportSectionResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReportSection::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSection()
    {
        $section = $this->fakeAssessmentReportSection();
        $request = [
            'position'      => $this->faker->numberBetween(1, 100),
            'heading'       => $this->faker->text(),
            'heading_style' => $this->faker->randomElement(AssessmentReportHeadingStyles::values()),
            'heading_color' => $this->faker->numberBetween(0, 16777215),
            'text'          => $this->faker->text(),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionsController@update', [
            'assessment_report_id' => $section->assessment_report_id,
            'section_id'           => $section->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportSection::findOrFail($section->id);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSection()
    {
        $section = $this->fakeAssessmentReportSection();
        $url     = action('AssessmentReports\AssessmentReportSectionsController@destroy', [
            'assessment_report_id' => $section->assessment_report_id,
            'section_id'           => $section->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSection::findOrFail($section->id);
    }
}
