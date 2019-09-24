<?php

namespace App\Components\AssessmentReports\Services;

use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use App\Components\AssessmentReports\Models\AssessmentReportEntity;
use App\Components\AssessmentReports\Models\VO\AbstractAssessmentReportData;
use Illuminate\Support\Collection;

/**
 * Class AssessmentReportEntityService
 *
 * @package App\Components\AssessmentReports\Services
 */
abstract class AssessmentReportEntityService
{
    /** @var AssessmentReportsServiceInterface */
    private $assessmentReportsService = null;

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
     * Returns assessment report entities.
     *
     * @param int $assessmentReportId Assessment report identifier.
     *
     * @return Collection|mixed
     */
    public function getEntities(int $assessmentReportId): Collection
    {
        $query = call_user_func([$this->getEntityClass(), 'whereAssessmentReportId'], $assessmentReportId);
        $query = call_user_func([$query, 'orderBy'], 'position');

        return call_user_func([$query, 'get']);
    }

    /**
     * Returns entity by its id and given assessment report id.
     *
     * @param int $assessmentReportId Assessment report identifier.
     * @param int $entityId           Entity identifier.
     *
     * @return AssessmentReportEntity
     */
    public function getEntity(int $assessmentReportId, int $entityId): AssessmentReportEntity
    {
        $entity = call_user_func([$this->getEntityClass(), 'whereAssessmentReportId'], $assessmentReportId);

        return call_user_func([$entity, 'findOrFail'], $entityId);
    }

    /**
     * Creates assessment report entity.
     *
     * @param AbstractAssessmentReportData $data               Data for create.
     * @param int                          $assessmentReportId Assessment report identifier.
     *
     * @return AssessmentReportEntity
     */
    public function create(
        AbstractAssessmentReportData $data,
        int $assessmentReportId
    ): AssessmentReportEntity {
        $this->commonService()->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        $assessmentReport = $this->commonService()->getAssessmentReport($assessmentReportId);

        /** @var AssessmentReportEntity $entity */
        $entity = $assessmentReport->{$this->getEntityRelation()}()
            ->create($data->toArray());

        event(new AssessmentReportEntityUpdated($assessmentReportId));

        return $entity;
    }

    /**
     * Updates assessment report entity.
     *
     * @param AbstractAssessmentReportData $data               Data for update.
     * @param int                          $assessmentReportId Assessment report identifier.
     * @param int                          $entityId           Updated entity identifier.
     *
     * @return AssessmentReportEntity
     */
    public function update(
        AbstractAssessmentReportData $data,
        int $assessmentReportId,
        int $entityId
    ): AssessmentReportEntity {
        $this->commonService()->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        $entity = $this->getEntity($assessmentReportId, $entityId);
        $entity->update($data->toArray());

        event(new AssessmentReportEntityUpdated($assessmentReportId));

        return $entity;
    }

    /**
     * Removes assessment report entity.
     *
     * @param int $assessmentReportId Assessment report identifier.
     * @param int $entityId           Deleted entity identifier.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete(
        int $assessmentReportId,
        int $entityId
    ): void {
        $this->commonService()->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReportId);

        $this->getEntity($assessmentReportId, $entityId)
            ->delete();

        event(new AssessmentReportEntityUpdated($assessmentReportId));
    }

    /**
     * Returns class name of entity which service is working with.
     *
     * @return string
     */
    abstract protected function getEntityClass(): string;

    /**
     * Returns relation between assessment report section and entity.
     *
     * @return string
     */
    abstract protected function getEntityRelation(): string;
}
