<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReportSectionImage;
use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionImageData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionImagesService;
use App\Components\Photos\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportSectionImagesServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportSectionImagesServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportSectionImagesService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportSectionImagesService::class);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionImage()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::IMAGE);
        $data    = new AssessmentReportSectionImageData([
            'photo_id'      => $photo->id,
            'caption'       => $this->faker->words(3, true),
            'desired_width' => $this->faker->numberBetween(256, 4096),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $image = $this->service->create($data, $section->assessment_report_id, $section->id);

        self::assertEquals($section->id, $image->assessment_report_section_id);
        self::compareDataWithModel($data->toArray(), $image);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionImageWhenAssessmentReportIsApproved()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::IMAGE,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        $data    = new AssessmentReportSectionImageData([
            'photo_id'      => $photo->id,
            'caption'       => $this->faker->words(3, true),
            'desired_width' => $this->faker->numberBetween(256, 4096),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionImageWhenSectionIsNonImage()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::TEXT);
        $data    = new AssessmentReportSectionImageData([
            'photo_id'      => $photo->id,
            'caption'       => $this->faker->words(3, true),
            'desired_width' => $this->faker->numberBetween(256, 4096),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionImage()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::IMAGE);
        /** @var AssessmentReportSectionImage $image */
        $image = factory(AssessmentReportSectionImage::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data  = new AssessmentReportSectionImageData([
            'photo_id'      => $photo->id,
            'caption'       => $this->faker->words(3, true),
            'desired_width' => $this->faker->numberBetween(256, 4096),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->update(
            $data,
            $image->section->assessment_report_id,
            $image->section->id,
            $image->id
        );

        $reloaded = AssessmentReportSectionImage::findOrFail($image->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportSectionImageWhenAssessmentReportIsApproved()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::IMAGE,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionImage $image */
        $image = factory(AssessmentReportSectionImage::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data  = new AssessmentReportSectionImageData([
            'photo_id'      => $photo->id,
            'caption'       => $this->faker->words(3, true),
            'desired_width' => $this->faker->numberBetween(256, 4096),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update(
            $data,
            $image->section->assessment_report_id,
            $image->section->id,
            $image->id
        );
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionImage()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::IMAGE);
        /** @var AssessmentReportSectionImage $image */
        $image = factory(AssessmentReportSectionImage::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->delete(
            $image->section->assessment_report_id,
            $image->section->id,
            $image->id
        );

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionImage::findOrFail($image->id);
    }

    /**
     * @throws \Exception
     */
    public function testFailToDeleteAssessmentReportSectionImageWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::IMAGE,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionImage $image */
        $image = factory(AssessmentReportSectionImage::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete(
            $image->section->assessment_report_id,
            $image->section->id,
            $image->id
        );
    }
}
