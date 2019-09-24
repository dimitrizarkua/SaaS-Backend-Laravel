<?php

namespace App\Components\Jobs\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Events\JobCreated;
use App\Components\Jobs\Events\JobDeleted;
use App\Components\Jobs\Events\JobPinToggled;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Interfaces\JobTasksServiceInterface;
use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Jobs\Models\VO\JobCreationData;
use App\Components\Jobs\Models\VO\JobTaskData;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Helpers\DateHelper;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class JobsService
 *
 * @package App\Components\Jobs\Services
 */
class JobsService implements JobsServiceInterface
{
    /**
     * @var JobUsersServiceInterface
     */
    private $jobUserService;

    /**
     * JobsService constructor.
     *
     * @param JobUsersServiceInterface $jobUserService
     */
    public function __construct(JobUsersServiceInterface $jobUserService)
    {
        $this->jobUserService = $jobUserService;
    }

    /**
     * @inheritdoc
     *
     * @return \App\Components\Jobs\Models\Job
     * @throws \Throwable
     */
    public function createJob(
        JobCreationData $data = null,
        string $jobStatus = JobStatuses::NEW,
        int $userId = null,
        bool $autoAssign = true
    ): Job {

        $creationData = null !== $data ? $data->toArray() : [];

        if (null !== $data && $data->insurer_id) {
            $contract = app()->make(InsurerContractsInterface::class)
                ->getActiveContractForInsurer(Contact::find($data->insurer_id));
            if ($contract) {
                $creationData['insurer_contract_id'] = $contract->id;
            } else {
                throw new NotAllowedException('The insurer has no active contract.');
            }
        }

        $job = DB::transaction(function () use ($creationData, $jobStatus, $userId, $autoAssign) {
            $job = new Job($creationData);
            $job->updateTouchedAt();
            $job->changeStatus($jobStatus, null, $userId);

            /** @var JobTaskType[] $autoTasksTypes */
            $autoTasksTypes = JobTaskType::query()
                ->where('auto_create', true)
                ->get();

            $jobTaskService = app()->make(JobTasksServiceInterface::class);
            foreach ($autoTasksTypes as $tasksType) {
                $jobTaskData = new JobTaskData([
                    'job_task_type_id' => $tasksType->id,
                    'name'             => $tasksType->name,
                ]);
                if (null !== $tasksType->kpi_hours) {
                    $jobTaskData->kpi_missed_at = DateHelper::addWorkHours(Carbon::now(), $tasksType->kpi_hours);
                }

                $jobTaskService->createTask($jobTaskData, $job->id, $userId);
            }

            if (null !== $userId && true === $autoAssign) {
                $this->jobUserService->assignToUser($job->id, $userId);
            }

            return $job;
        });
        event(new JobCreated($job, $userId));

        return $job;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateJob(Job $job, array $data): Job
    {
        if (isset($data['insurer_id'])) {
            if ($job->insurer_id) {
                if ($data['insurer_id'] !== $job->insurer_id) {
                    throw new NotAllowedException('The insurer of this job already set.');
                }
            } else {
                $contact  = Contact::findOrFail($data['insurer_id']);
                $contract = app()->make(InsurerContractsInterface::class)
                    ->getActiveContractForInsurer($contact);

                if (null === $contract) {
                    throw new NotAllowedException('The insurer has no active contract.');
                }

                $data['insurer_contract_id'] = $contract->id;
            }
        }

        $job->update($data);

        return $job;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    public function deleteJob(Job $job, int $userId = null): void
    {
        $job->delete();

        event(new JobDeleted($job, $userId));
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
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getJobStatus(int $jobId): string
    {
        $job = $this->getJob($jobId);

        return $job->getCurrentStatus();
    }

    /**
     * @inheritdoc
     */
    public function pin(int $jobId, bool $value = true): void
    {
        $job = $this->getJob($jobId);

        if ($job->pinned_at == $value) {
            return;
        }

        $job->update(['pinned_at' => $value ? Carbon::now() : null]);

        event(new JobPinToggled());
    }

    /**
     * @inheritdoc
     */
    public function touch(int $jobId): void
    {
        DB::table('jobs')
            ->where('id', $jobId)
            ->update(['touched_at' => 'now()']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function linkJobs(int $jobId, int $linkedJobId): void
    {
        try {
            DB::transaction(function () use ($jobId, $linkedJobId) {
                $job       = $this->getJob($jobId);
                $linkedJob = $this->getJob($linkedJobId);

                $job->linkedJobs()->attach($linkedJobId);
                $linkedJob->linkedJobs()->attach($jobId);
            });
        } catch (NotAllowedException $exception) {
            throw new NotAllowedException($exception->getMessage());
        } catch (Exception $exception) {
            throw new NotAllowedException('This job is already linked to specified job.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function unlinkJobs(int $jobId, int $linkedJobId): void
    {
        DB::transaction(function () use ($jobId, $linkedJobId) {
            $job       = $this->getJob($jobId);
            $linkedJob = $this->getJob($linkedJobId);

            $job->linkedJobs()->detach($linkedJobId);
            $linkedJob->linkedJobs()->detach($jobId);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedJobs(int $jobId): Collection
    {
        return $this->getJob($jobId)->linkedJobs;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function snoozeJob(int $jobId, string $date): void
    {
        $job = $this->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->update(['snoozed_until' => $date]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unsnoozeJob(int $jobId): void
    {
        $job = $this->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        if (null !== $job->snoozed_until) {
            $job->update(['snoozed_until' => null]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getJobCostingCounters(int $jobId): array
    {
        $materials         = JobMaterial::query()->where('job_id', $jobId)->count();
        $equipment         = JobEquipment::query()->where('job_id', $jobId)->count();
        $labours           = JobLabour::query()->where('job_id', $jobId)->count();
        $allowances        = JobAllowance::query()->where('job_id', $jobId)->count();
        $reimbursements    = JobReimbursement::query()->where('job_id', $jobId)->count();
        $lahaCompensations = JobLahaCompensation::query()->where('job_id', $jobId)->count();
        $purchaseOrders    = PurchaseOrder::query()->where('job_id', $jobId)->count();

        return [
            'materials'       => $materials,
            'equipment'       => $equipment,
            'time'            => $labours + $allowances + $reimbursements + $lahaCompensations,
            'purchase_orders' => $purchaseOrders,
        ];
    }
}
