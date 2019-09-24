<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto;
use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionPhotoData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionPhotosService;
use App\Components\Photos\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportSectionPhotosServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportSectionPhotosServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportSectionPhotosService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportSectionPhotosService::class);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionPhoto()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        $data    = new AssessmentReportSectionPhotoData([
            'photo_id' => $photo->id,
            'position' => $this->faker->randomNumber(1),
            'caption'  => $this->faker->text(),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $photo = $this->service->create($data, $section->assessment_report_id, $section->id);

        self::assertEquals($section->id, $photo->assessment_report_section_id);
        self::compareDataWithModel($data->toArray(), $photo);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionPhotoWhenAssessmentReportIsApproved()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::PHOTOS,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        $data    = new AssessmentReportSectionPhotoData([
            'photo_id' => $photo->id,
            'position' => $this->faker->randomNumber(1),
            'caption'  => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionPhotoWhenSectionIsNonPhoto()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::TEXT);
        $data    = new AssessmentReportSectionPhotoData([
            'photo_id' => $photo->id,
            'position' => $this->faker->randomNumber(1),
            'caption'  => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionPhoto()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        /** @var AssessmentReportSectionPhoto $sectionPhoto */
        $sectionPhoto = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data         = new AssessmentReportSectionPhotoData([
            'photo_id' => $photo->id,
            'position' => $this->faker->randomNumber(1),
            'caption'  => $this->faker->text(),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->update(
            $data,
            $sectionPhoto->section->assessment_report_id,
            $sectionPhoto->section->id,
            $sectionPhoto->id
        );

        $reloaded = AssessmentReportSectionPhoto::findOrFail($sectionPhoto->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportSectionPhotoWhenAssessmentReportIsApproved()
    {
        /** @var Photo $photo */
        $photo   = factory(Photo::class)->create();
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::PHOTOS,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionPhoto $sectionPhoto */
        $sectionPhoto = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data         = new AssessmentReportSectionPhotoData([
            'photo_id' => $photo->id,
            'position' => $this->faker->randomNumber(1),
            'caption'  => $this->faker->text(),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update(
            $data,
            $sectionPhoto->section->assessment_report_id,
            $sectionPhoto->section->id,
            $sectionPhoto->id
        );
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionPhoto()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        /** @var AssessmentReportSectionPhoto $photo */
        $photo = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->delete(
            $photo->section->assessment_report_id,
            $photo->section->id,
            $photo->id
        );

        self::expectException(ModelNotFoundException::class);
        AssessmentReportSectionPhoto::findOrFail($photo->id);
    }

    /**
     * @throws \Exception
     */
    public function testFailToDeleteAssessmentReportSectionPhotoWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::PHOTOS,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionPhoto $photo */
        $photo = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete(
            $photo->section->assessment_report_id,
            $photo->section->id,
            $photo->id
        );
    }
}
