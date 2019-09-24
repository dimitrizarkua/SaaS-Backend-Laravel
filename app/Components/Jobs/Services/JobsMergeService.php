<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Events\JobStatusChanged;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobAreasServiceInterface;
use App\Components\Jobs\Interfaces\JobEquipmentServiceInterface;
use App\Components\Jobs\Interfaces\JobLabourServiceInterface;
use App\Components\Jobs\Interfaces\JobMaterialsServiceInterface;
use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobRoom;
use App\Components\Jobs\Models\VO\CreateJobEquipmentData;
use App\Components\Jobs\Models\VO\JobLabourData;
use App\Components\Jobs\Models\VO\JobMaterialData;
use App\Components\Jobs\Models\VO\JobRoomData;
use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Models\NoteData;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class JobsMergeService
 *
 * @param int $sourceJobId      Source job identifier.
 * @param int $destinationJobId Destination job identifier.
 * @param int $userId           User identifier.
 *
 * @package App\Components\Jobs\Services
 */
class JobsMergeService
{
    private const SOURCE_TEMPLATE_STRING      = 'Job #%s merged into #%s job.';
    private const DESTINATION_TEMPLATE_STRING = 'Merged into job #%s from #%s job.';

    /** @var \App\Components\Notes\Interfaces\NotesServiceInterface */
    private $notesService;

    /** @var \App\Components\Jobs\Interfaces\JobNotesServiceInterface */
    private $jobNotesService;

    /** @var \App\Components\Jobs\Interfaces\JobEquipmentServiceInterface */
    private $jobEquipmentService;

    /** @var \App\Components\Jobs\Interfaces\JobMaterialsServiceInterface */
    private $jobMaterialService;

    /** @var \App\Components\Jobs\Interfaces\JobLabourServiceInterface */
    private $jobLabourService;

    /** @var \App\Components\Jobs\Interfaces\JobAreasServiceInterface */
    private $jobAreasService;

    /**
     * JobMergeService constructor.
     *
     * @param \App\Components\Notes\Interfaces\NotesServiceInterface       $notesService
     * @param \App\Components\Jobs\Interfaces\JobNotesServiceInterface     $jobNotesService
     * @param \App\Components\Jobs\Interfaces\JobEquipmentServiceInterface $jobEquipmentService
     * @param \App\Components\Jobs\Interfaces\JobMaterialsServiceInterface $jobMaterialService
     * @param \App\Components\Jobs\Interfaces\JobLabourServiceInterface    $jobLabourService
     * @param \App\Components\Jobs\Interfaces\JobAreasServiceInterface     $jobAreasService
     */
    public function __construct(
        NotesServiceInterface $notesService,
        JobNotesServiceInterface $jobNotesService,
        JobEquipmentServiceInterface $jobEquipmentService,
        JobMaterialsServiceInterface $jobMaterialService,
        JobLabourServiceInterface $jobLabourService,
        JobAreasServiceInterface $jobAreasService
    ) {
        $this->notesService        = $notesService;
        $this->jobNotesService     = $jobNotesService;
        $this->jobEquipmentService = $jobEquipmentService;
        $this->jobMaterialService  = $jobMaterialService;
        $this->jobLabourService    = $jobLabourService;
        $this->jobAreasService     = $jobAreasService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getJob(int $jobId): Job
    {
        return Job::findOrFail($jobId);
    }

    /**
     * Merge one job into another.
     * When merging all attachments are copied from the source to the destination job
     * including notes, messages, documents, photos, materials etc.
     * Then source job will be set to the `Closed` state.
     *
     * @param int $sourceJobId      Source job identifier.
     * @param int $destinationJobId Destination job identifier.
     * @param int $userId           User identifier.
     *
     * @throws \Throwable
     */
    public function mergeJobs(int $sourceJobId, int $destinationJobId, int $userId): void
    {
        $sourceJob      = $this->getJob($sourceJobId);
        $destinationJob = $this->getJob($destinationJobId);

        if ($destinationJob->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $sourceJob->mergedJobs()->attach($destinationJob->id);
        } catch (Exception $e) {
            //have already been attached
        }

        $statusChanged = DB::transaction(function () use ($sourceJob, $destinationJob, $userId) {
            $this->mergeNotes($sourceJob, $destinationJob);
            $this->mergeMessages($sourceJob, $destinationJob);
            $this->mergeDocuments($sourceJob, $destinationJob);
            $this->mergePhotos($sourceJob, $destinationJob);
            $this->mergeRooms($sourceJob, $destinationJob);
            $this->mergeLabours($sourceJob, $destinationJob, $userId);
            $this->mergeEquipment($sourceJob, $destinationJob, $userId);
            $this->mergeMaterials($sourceJob, $destinationJob, $userId);
            $this->mergeAllowances($sourceJob, $destinationJob, $userId);
            $this->mergeReimbursements($sourceJob, $destinationJob, $userId);
            $this->mergeLAHACompensations($sourceJob, $destinationJob, $userId);

            /** todo
             * Job Costings: Assessment Reports
             * Inventories and all associated data
             */

            $sourceNoteData = $this->getSourceNoteData($sourceJob->id, $destinationJob->id, $userId);
            $sourceNote     = $this->notesService->addNote($sourceNoteData);
            $this->jobNotesService->addNote($destinationJob->id, $sourceNote->id, null, false);

            $dstNoteData = $this->getDestinationNoteData($sourceJob->id, $destinationJob->id, $userId);
            $dstNote     = $this->notesService->addNote($dstNoteData);
            $this->jobNotesService->addNote($sourceJob->id, $dstNote->id, null, false);

            if (JobStatuses::CLOSED !== $sourceJob->getCurrentStatus()) {
                $sourceJob->changeStatus(JobStatuses::CLOSED, null, $userId);

                return true;
            }

            return false;
        });

        if ($statusChanged) {
            event(new JobStatusChanged($sourceJob, $userId));
        }
    }

    /**
     * Merges (copy) mergeable notes from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     */
    private function mergeNotes(Job $sourceJob, Job $destinationJob): void
    {
        $srcIds = $sourceJob->notes()
            ->where('mergeable', '=', true)
            ->pluck('id');

        $dstIds = $destinationJob->notes()
            ->pluck('id')
            ->toArray();

        $shouldBeAttachedIds = $srcIds->diff($dstIds)
            ->all();

        $destinationJob->notes()->attach($shouldBeAttachedIds);
    }

    /**
     * Merges (copy) messages from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     */
    private function mergeMessages(Job $sourceJob, Job $destinationJob): void
    {
        $srcIds = $sourceJob->messages()
            ->pluck('id');

        $dstIds = $destinationJob->messages()
            ->pluck('id')
            ->toArray();

        $shouldBeAttachedIds = $srcIds->diff($dstIds)
            ->all();

        $destinationJob->messages()->attach($shouldBeAttachedIds);
    }

    /**
     * Merges (copy) documents from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     */
    private function mergeDocuments(Job $sourceJob, Job $destinationJob): void
    {
        $srcDocuments = $sourceJob->documents()->get();

        $dstIds = $destinationJob->documents()
            ->pluck('id')
            ->toArray();

        $shouldBeAttached = [];
        foreach ($srcDocuments as $document) {
            if (!in_array($document->id, $dstIds)) {
                $shouldBeAttached[$document->id] = ['type' => $document->pivot->type];
            }
        }

        $destinationJob->documents()->attach($shouldBeAttached);
    }

    /**
     * Merges (copy) photos from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     */
    private function mergePhotos(Job $sourceJob, Job $destinationJob): void
    {
        $srcPhotos = $sourceJob->photos()->get();

        $dstIds = $destinationJob->photos()
            ->pluck('id')
            ->toArray();

        $shouldBeAttached = [];
        foreach ($srcPhotos as $photo) {
            if (!in_array($photo->id, $dstIds)) {
                $shouldBeAttached[$photo->id] = [
                    'creator_id'     => $photo->pivot->creator_id,
                    'modified_by_id' => $photo->pivot->modified_by_id,
                    'description'    => $photo->pivot->description,
                    'created_at'     => $photo->pivot->created_at,
                    'updated_at'     => $photo->pivot->updated_at,
                ];
            }
        }

        $destinationJob->photos()->attach($shouldBeAttached);
    }


    /**
     * Merges (copy) rooms from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     *
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    private function mergeRooms(Job $sourceJob, Job $destinationJob): void
    {
        /** @var JobRoom $jobRoom */
        foreach ($sourceJob->jobRooms as $jobRoom) {
            $jobRoomData = new JobRoomData($jobRoom->toArray());
            $this->jobAreasService->addRoom($jobRoomData, $destinationJob->id);
        }
    }

    /**
     * Merges (copy) labour from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     * @param int                             $userId
     *
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    private function mergeLabours(Job $sourceJob, Job $destinationJob, int $userId): void
    {
        /** @var JobLabour $jobLabour */
        foreach ($sourceJob->labours as $jobLabour) {
            $jobLabourData                  = new JobLabourData($jobLabour->toArray());
            $jobLabourData->invoice_item_id = null;
            $jobLabourData->job_id          = $destinationJob->id;
            $jobLabourData->creator_id      = $userId;
            $this->jobLabourService->createJobLabour($jobLabourData);
        }
    }

    /**
     * Merges (copy) equipment from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     * @param int                             $userId
     *
     * @throws \JsonMapper_Exception
     */
    private function mergeEquipment(Job $sourceJob, Job $destinationJob, int $userId): void
    {
        /** @var JobEquipment $jobEquipment */
        foreach ($sourceJob->equipment as $jobEquipment) {
            $jobEquipmentData = new CreateJobEquipmentData($jobEquipment->toArray());

            $this->jobEquipmentService->createJobEquipment(
                $jobEquipmentData,
                $destinationJob->id,
                $userId
            );
        }
    }

    /**
     * Merges (copy) materials from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     *
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    private function mergeMaterials(Job $sourceJob, Job $destinationJob, int $userId): void
    {
        /** @var JobMaterial $jobMaterial */
        foreach ($sourceJob->materials as $jobMaterial) {
            // todo is_used_on_ar ???
            $jobMaterialData                  = new JobMaterialData($jobMaterial->toArray());
            $jobMaterialData->invoice_item_id = null;
            $jobMaterialData->job_id          = $destinationJob->id;
            $jobMaterialData->creator_id      = $userId;
            $this->jobMaterialService->create($jobMaterialData);
        }
    }

    /**
     * Merges (copy) allowances from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     */
    private function mergeAllowances(Job $sourceJob, Job $destinationJob, int $userId): void
    {
        $models = [];
        /** @var JobAllowance $jobAllowance */
        foreach ($sourceJob->allowances as $jobAllowance) {
            $new             = new JobAllowance($jobAllowance->toArray());
            $new->job_id     = $destinationJob->id;
            $new->creator_id = $userId;
            // todo is_used_on_ar ???
            $models[] = $new;
        }

        $destinationJob->allowances()->saveMany($models);
    }

    /**
     * Merges (copy) Reimbursements from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     */
    private function mergeReimbursements(Job $sourceJob, Job $destinationJob, int $userId): void
    {
        $models = [];

        /** @var JobReimbursement $jobReimbursment */
        foreach ($sourceJob->reimbursements as $jobReimbursement) {
            $new                  = new JobReimbursement($jobReimbursement->toArray());
            $new->invoice_item_id = null;
            $new->job_id          = $destinationJob->id;
            $new->creator_id      = $userId;
            // todo is_used_on_ar ???
            $models[] = $new;
        }

        $destinationJob->reimbursements()->saveMany($models);
    }

    /**
     * Merges (copy) LAHA compensation from source to destination.
     *
     * @param \App\Components\Jobs\Models\Job $sourceJob
     * @param \App\Components\Jobs\Models\Job $destinationJob
     */
    private function mergeLAHACompensations(Job $sourceJob, Job $destinationJob, int $userId): void
    {
        $models = [];
        /** @var JobLahaCompensation $jobLahas */
        foreach ($sourceJob->compensations as $jobLaha) {
            $new             = new JobLahaCompensation($jobLaha->toArray());
            $new->job_id     = $destinationJob->id;
            $new->creator_id = $userId;
            // todo is_used_on_ar ???
            $models[] = $new;
        }

        $destinationJob->compensations()->saveMany($models);
    }

    /**
     * @param int $sourceJobId
     * @param int $destinationJobId
     * @param int $userId
     *
     * @return \App\Components\Notes\Models\NoteData
     */
    private function getSourceNoteData(int $sourceJobId, int $destinationJobId, int $userId): NoteData
    {
        $noteTemplateText = sprintf(self::SOURCE_TEMPLATE_STRING, $sourceJobId, $destinationJobId);

        return new NoteData($noteTemplateText, $userId);
    }

    /**
     * @param int $sourceJobId
     * @param int $destinationJobId
     * @param int $userId
     *
     * @return \App\Components\Notes\Models\NoteData
     */
    private function getDestinationNoteData(int $sourceJobId, int $destinationJobId, int $userId): NoteData
    {
        $noteTemplateText = sprintf(self::DESTINATION_TEMPLATE_STRING, $destinationJobId, $sourceJobId);

        return new NoteData($noteTemplateText, $userId);
    }
}
