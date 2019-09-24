<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobContactsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobContactAssignment;
use App\Components\Jobs\Models\JobContactAssignmentType;
use Illuminate\Support\Collection;

/**
 * Class JobContactsService
 *
 * @package App\Components\Jobs\Services
 */
class JobContactsService extends JobsEntityService implements JobContactsServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function assignContact(
        int $jobId,
        int $contactId,
        int $typeId,
        bool $invoiceTo = false,
        ?int $assignerId = null
    ): void {
        /** @var Job $job */
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        /** @var JobContactAssignmentType $type */
        $type = JobContactAssignmentType::findOrFail($typeId);
        $this->checkExistingAssignments($job, $type, $contactId);

        // Remember that if you set `invoice_to=true` for a job, then database trigger will set `invoice_to=false`
        // for all previously assigned contacts to this job.
        // A job can have only one contact assignment with `invoice_to=true`!

        $job->assignedContacts()->attach($contactId, [
            'job_assignment_type_id' => $type->id,
            'invoice_to'             => $invoiceTo,
            'assigner_id'            => $assignerId,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function updateContactAssignment(
        int $jobId,
        int $contactId,
        int $typeId,
        bool $invoiceTo = false,
        ?int $assignerId = null,
        ?int $newTypeId = null
    ): void {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        if (null !== $newTypeId) {
            $newType = JobContactAssignmentType::findOrFail($newTypeId);
            $this->checkExistingAssignments($job, $newType, $contactId);
        }

        $job->assignedContacts()->updateExistingPivot($contactId, [
            'job_assignment_type_id' => $newTypeId ?? $typeId,
            'invoice_to'             => $invoiceTo,
            'assigner_id'            => $assignerId,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unassignContact(int $jobId, int $contactId, int $typeId): void
    {
        /** @var Job $job */
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->assignedContacts()
            ->newPivotStatement()
            ->where('assignee_contact_id', $contactId)
            ->where('job_assignment_type_id', $typeId)
            ->delete();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getAssignedContacts(int $jobId, ?int $typeId = null): Collection
    {
        $query = $this->jobsService()->getJob($jobId)->assignedContacts();
        if (null !== $typeId) {
            $query->wherePivot('job_assignment_type_id', '=', $typeId);
        }
        $query->with([
            'tags',
            'person',
            'company',
            'assignmentTypes',
            'avatar',
        ]);

        return $query->get();
    }

    /**
     * @param \App\Components\Jobs\Models\Job                      $job
     * @param \App\Components\Jobs\Models\JobContactAssignmentType $type
     * @param int                                                  $contactId
     *
     * @throws NotAllowedException
     */
    private function checkExistingAssignments(Job $job, JobContactAssignmentType $type, int $contactId)
    {
        /** @var JobContactAssignment $existingAssignment */
        $existingAssignment = JobContactAssignment::query()
            ->where([
                'job_id'                 => $job->id,
                'job_assignment_type_id' => $type->id,
            ])
            ->first();

        if (null !== $existingAssignment) {
            if ($contactId === $existingAssignment->assignee_contact_id) {
                throw new NotAllowedException(
                    sprintf('This contact already assigned as %s to specified job.', $type->name)
                );
            }

            if ($type->is_unique) {
                throw new NotAllowedException(
                    sprintf('Another contact has been already assigned as %s to specified job.', $type->name)
                );
            }
        }
    }
}
