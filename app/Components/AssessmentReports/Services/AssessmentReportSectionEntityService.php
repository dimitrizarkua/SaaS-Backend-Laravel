<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use App\Components\AssessmentReports\Models\AssessmentReportSectionEntity;
use App\Components\AssessmentReports\Models\VO\AbstractAssessmentReportData;

/**
 * Class AssessmentReportSectionEntityService
 *
 * @package App\Components\AssessmentReports\Services
 */
abstract class AssessmentReportSectionEntityService
{
    /** @var AssessmentReportsServiceInterface */
    private $assessmentReportsService = null;

    /** @var AssessmentReportSectionsService */
    private $sectionsService = null;

    /**
     * Returns instance of AssessmentReportsService.
     *
     * @return AssessmentReportsServiceInterface
     */
    protected function commonService(): AssessmentReportsServiceInterface
    {
        if (!$this->assessmentReportsService) {
            $this->assessmentReportsService = app()->make(AssessmentReportsServiceInterface::class);
        }

        return $this->assessmentReportsService;
    }

    /**
     * Returns instance of AssessmentReportSectionsService.
     *
     * @return AssessmentReportSectionsService
     */
    protected function sectionsService(): AssessmentReportSectionsService
    {
        if (!$this->sectionsService) {
            $this->sectionsService = app()->make(AssessmentReportSectionsService::class);
        }

        return $this->sectionsService;
    }

    /**
     * Returns entity by its id and given section id.
     *
     * @param int $sectionId Assessment report section identifier.
     * @param int $entityId  Entity identifier.
     *
     * @return AssessmentReportSectionEntity
     */
    public function getEntity(int $sectionId, int $entityId): AssessmentReportSectionEntity
    {
        $entity = call_user_func([$this->getEntityClass(), 'whereAssessmentReportSectionId'], $sectionId);

        return call_user_func([$entity, 'findOrFail'], $entityId);
    }

    /**
     * Creates section entity.
     *
     * @param AbstractAssessmentReportData $data               Data for create.
     * @param int                          $assessmentReportId Assessment report identifier.
     * @param int                          $sectionId          Assessment report section identifier.
     *
     * @return AssessmentReportSectionEntity
     */
    public function create(
        AbstractAssessmentReportData $data,
        int $assessmentReportId,
        int $sectionId
    ): AssessmentReportSectionEntity {
        $this->commonService()->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        /** @var \App\Components\AssessmentReports\Models\AssessmentReportSection $section */
        $section = $this->sectionsService()->getEntity($assessmentReportId, $sectionId);
        if (!$this->matchTypes($section->type)) {
            throw new NotAllowedException('Assessment report section has different type.');
        }

        /** @var AssessmentReportSectionEntity $entity */
        $entity = $section->{$this->getEntityRelation()}()
            ->create($data->toArray());

        event(new AssessmentReportSectionEntityUpdated($assessmentReportId));

        return $entity;
    }

    /**
     * Updates section entity.
     *
     * @param AbstractAssessmentReportData $data               Data for update.
     * @param int                          $assessmentReportId Assessment report identifier.
     * @param int                          $sectionId          Assessment report section identifier.
     * @param int                          $entityId           Updated entity identifier.
     *
     * @return AssessmentReportSectionEntity
     */
    public function update(
        AbstractAssessmentReportData $data,
        int $assessmentReportId,
        int $sectionId,
        int $entityId
    ): AssessmentReportSectionEntity {
        $this->commonService()->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        $entity = $this->getEntity($sectionId, $entityId);
        $entity->update($data->toArray());

        event(new AssessmentReportSectionEntityUpdated($assessmentReportId));

        return $entity;
    }

    /**
     * Removes section entity.
     *
     * @param int $assessmentReportId Assessment report identifier.
     * @param int $sectionId          Assessment report section identifier.
     * @param int $entityId           Deleted entity identifier.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete(
        int $assessmentReportId,
        int $sectionId,
        int $entityId
    ): void {
        $this->commonService()->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        $entity = $this->getEntity($sectionId, $entityId);
        $entity->delete();

        event(new AssessmentReportSectionEntityUpdated($assessmentReportId));
    }

    /**
     * Returns class name of entity which service is working with.
     *
     * @return string
     */
    abstract protected function getEntityClass(): string;

    /**
     * Matches section type with given type.
     *
     * @param string $type
     *
     * @return bool
     */
    abstract protected function matchTypes(string $type): bool;

    /**
     * Returns relation between assessment report section and entity.
     *
     * @return string
     */
    abstract protected function getEntityRelation(): string;
}
