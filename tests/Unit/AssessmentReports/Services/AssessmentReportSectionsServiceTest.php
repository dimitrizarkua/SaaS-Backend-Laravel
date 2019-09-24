<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportHeadingStyles;
use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportSectionsServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportSectionsServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportSectionsService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportSectionsService::class);
    }

    public function testGetAssessmentReportSections()
    {
        $count = $this->faker->numberBetween(1, 5);
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        factory(AssessmentReportSection::class, $count)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $reloaded = $this->service->getEntities($assessmentReport->id);

        self::assertCount($count, $reloaded);
    }

    public function testGetAssessmentReportSection()
    {
        /** @var AssessmentReportSection $section */
        $section = factory(AssessmentReportSection::class)->create();

        $reloaded = $this->service->getEntity($section->assessment_report_id, $section->id);

        self::compareDataWithModel($section->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportSection()
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $data             = new AssessmentReportSectionData([
            'type'          => $this->faker->randomElement(AssessmentReportSectionTypes::values()),
            'position'      => $this->faker->numberBetween(1, 100),
            'heading'       => $this->faker->text(),
            'heading_style' => $this->faker->randomElement(AssessmentReportHeadingStyles::values()),
            'heading_color' => $this->faker->numberBetween(0, 16777215),
            'text'          => $this->faker->text(),
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $section = $this->service->create($data, $assessmentReport->id);

        self::compareDataWithModel($data->toArray(), $section);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionWhenAssessmentReportIsApproved()
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::CLIENT_APPROVED);
        $data             = new AssessmentReportSectionData([
            'type'          => $this->faker->randomElement(AssessmentReportSectionTypes::values()),
            'position'      => $this->faker->numberBetween(1, 100),
            'heading'       => $this->faker->text(),
            'heading_style' => $this->faker->randomElement(AssessmentReportHeadingStyles::values()),
            'heading_color' => $this->faker->numberBetween(0, 16777215),
            'text'          => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $assessmentReport->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSection()
    {
        $section = $this->fakeAssessmentReportSection();
        $data    = new AssessmentReportSectionData([
            'position'      => $this->faker->numberBetween(1, 100),
            'heading'       => $this->faker->text(),
            'heading_style' => $this->faker->randomElement(AssessmentReportHeadingStyles::values()),
            'heading_color' => $this->faker->numberBetween(0, 16777215),
            'text'          => $this->faker->text(),
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->service->update($data, $section->assessment_report_id, $section->id);

        $reloaded = AssessmentReportSection::findOrFail($section->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportSectionWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(null, AssessmentReportStatuses::CLIENT_APPROVED);
        $data    = new AssessmentReportSectionData([
            'position'      => $this->faker->numberBetween(1, 100),
            'heading'       => $this->faker->text(),
            'heading_style' => $this->faker->randomElement(AssessmentReportHeadingStyles::values()),
            'heading_color' => $this->faker->numberBetween(0, 16777215),
            'text'          => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSection()
    {
        $section = $this->fakeAssessmentReportSection();

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->service->delete($section->assessment_report_id, $section->id);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSection::findOrFail($section->id);
    }

    /**
     * @throws \Exception
     */
    public function testFailToDeleteAssessmentReportSectionWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(null, AssessmentReportStatuses::CLIENT_APPROVED);

        self::expectException(NotAllowedException::class);
        $this->service->delete($section->assessment_report_id, $section->id);
    }
}
