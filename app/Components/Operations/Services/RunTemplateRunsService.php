<?php

namespace App\Components\Operations\Services;

use App\Components\Operations\Exceptions\NotAllowedException;
use App\Components\Operations\Interfaces\RunTemplateRunsServiceInterface;
use App\Components\Operations\Interfaces\RunTemplatesServiceInterface;
use App\Components\Operations\Models\JobRunTemplateRun;
use Illuminate\Support\Collection;

/**
 * Class RunTemplateRunsService
 *
 * @package App\Components\Operations\Services
 */
class RunTemplateRunsService implements RunTemplateRunsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTemplateRun(int $runId): JobRunTemplateRun
    {
        return JobRunTemplateRun::findOrFail($runId);
    }

    /**
     * {@inheritdoc}
     */
    public function listTemplateRuns(int $templateId): Collection
    {
        $template = app()->make(RunTemplatesServiceInterface::class)->getTemplate($templateId);

        return $template->runs()
            ->with('assignedUsers')
            ->with('assignedVehicles')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function createTemplateRun(int $templateId, string $name = null): JobRunTemplateRun
    {
        return JobRunTemplateRun::create([
            'job_run_template_id' => $templateId,
            'name'                => $name,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function deleteTemplateRun(int $runId): void
    {
        $run = $this->getTemplateRun($runId);

        try {
            $run->delete();
        } catch (\Exception $e) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function assignUser(int $runId, int $userId): void
    {
        $run = $this->getTemplateRun($runId);

        try {
            $run->assignedUsers()->attach($userId);
        } catch (\Exception $e) {
            throw new NotAllowedException('User is already assigned to this run.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unassignUser(int $runId, int $userId): void
    {
        $run = $this->getTemplateRun($runId);
        $run->assignedUsers()->detach($userId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function assignVehicle(int $runId, int $vehicleId): void
    {
        $run = $this->getTemplateRun($runId);

        try {
            $run->assignedVehicles()->attach($vehicleId);
        } catch (\Exception $e) {
            throw new NotAllowedException('Vehicle is already assigned to this run.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unassignVehicle(int $runId, int $vehicleId): void
    {
        $run = $this->getTemplateRun($runId);
        $run->assignedVehicles()->detach($vehicleId);
    }
}
