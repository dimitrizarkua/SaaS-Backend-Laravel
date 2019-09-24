<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReportSectionTextBlock;
use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionTextBlockData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionTextBlocksService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportSectionTextBlocksServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportSectionTextBlocksServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportSectionTextBlocksService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportSectionTextBlocksService::class);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionTextBlock()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes)
        );
        $data    = new AssessmentReportSectionTextBlockData([
            'position' => $this->faker->numberBetween(1, 100),
            'text'     => $this->faker->text(),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $textBlock = $this->service->create($data, $section->assessment_report_id, $section->id);

        self::assertEquals($section->id, $textBlock->assessment_report_section_id);
        self::compareDataWithModel($data->toArray(), $textBlock);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionTextBlockWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes),
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        $data    = new AssessmentReportSectionTextBlockData([
            'position' => $this->faker->numberBetween(1, 100),
            'text'     => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionTextBlockWhenSectionIsNonText()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        $data    = new AssessmentReportSectionTextBlockData([
            'position' => $this->faker->numberBetween(1, 100),
            'text'     => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionTextBlock()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes)
        );
        /** @var AssessmentReportSectionTextBlock $textBlock */
        $textBlock = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data      = new AssessmentReportSectionTextBlockData([
            'position' => $this->faker->numberBetween(1, 100),
            'text'     => $this->faker->text(),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->update(
            $data,
            $textBlock->section->assessment_report_id,
            $textBlock->section->id,
            $textBlock->id
        );

        $reloaded = AssessmentReportSectionTextBlock::findOrFail($textBlock->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportSectionTextBlockWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes),
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionTextBlock $textBlock */
        $textBlock = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data      = new AssessmentReportSectionTextBlockData([
            'position' => $this->faker->numberBetween(1, 100),
            'text'     => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update(
            $data,
            $textBlock->section->assessment_report_id,
            $textBlock->section->id,
            $textBlock->id
        );
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionTextBlock()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes)
        );
        /** @var AssessmentReportSectionTextBlock $textBlock */
        $textBlock = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->delete(
            $textBlock->section->assessment_report_id,
            $textBlock->section->id,
            $textBlock->id
        );

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionTextBlock::findOrFail($textBlock->id);
    }

    /**
     * @throws \Exception
     */
    public function testFailToDeleteAssessmentReportSectionTextBlockWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            $this->faker->randomElement(AssessmentReportSectionTypes::$textSectionTypes),
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionTextBlock $textBlock */
        $textBlock = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete(
            $textBlock->section->assessment_report_id,
            $textBlock->section->id,
            $textBlock->id
        );
    }
}
