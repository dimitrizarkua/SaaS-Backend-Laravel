<?php

namespace App\Components\Jobs\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface JobContactsServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobContactsServiceInterface
{
    /**
     * Allows to assign a contact to job with specific type, e.g. Customer or Loss Adjustor.
     *
     * @param int      $jobId      Job id.
     * @param int      $contactId  Contact Id.
     * @param int      $typeId     Assignment type id.
     * @param bool     $invoiceTo  Indicate whether contact that is being assigned should be invoiced or not.
     * @param int|null $assignerId Assigner id (user id).
     *
     * @see \App\Components\Jobs\Models\JobContactAssignmentType
     */
    public function assignContact(
        int $jobId,
        int $contactId,
        int $typeId,
        bool $invoiceTo = false,
        ?int $assignerId = null
    ): void;

    /**
     * Allows to unassign a contact from a job which as assigned with specific type earlier.
     *
     * @param int $jobId     Job id.
     * @param int $contactId Contact id.
     * @param int $typeId    Assignment type id.
     *
     * @see \App\Components\Jobs\Models\JobContactAssignmentType
     */
    public function unassignContact(int $jobId, int $contactId, int $typeId): void;

    /**
     * Allows to update existing contact assignment parameters.
     *
     * @param int      $jobId      Job id.
     * @param int      $contactId  Contact id.
     * @param int      $typeId     Assignment type id.
     * @param bool     $invoiceTo  Indicate whether contact that is being updated should be invoiced or not.
     * @param int|null $assignerId Assigner id (user id).
     * @param int|null $newTypeId  New assignment type id.
     */
    public function updateContactAssignment(
        int $jobId,
        int $contactId,
        int $typeId,
        bool $invoiceTo = false,
        ?int $assignerId = null,
        ? int $newTypeId = null
    ): void;

    /**
     * Allows to get all contacts assigned to a job and optionally filter the result set
     * by assignment type.
     *
     * @param int      $jobId  Job id.
     * @param int|null $typeId Assignment type id.
     *
     * @see \App\Components\Jobs\Models\JobContactAssignmentType
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAssignedContacts(int $jobId, ?int $typeId = null): Collection;
}
