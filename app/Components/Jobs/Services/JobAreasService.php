<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobAreasServiceInterface;
use App\Components\Jobs\Models\JobRoom;
use App\Components\Jobs\Models\VO\JobRoomData;
use Illuminate\Support\Collection;

/**
 * Class JobAreasService
 *
 * @package App\Components\Jobs\Services
 */
class JobAreasService extends JobsEntityService implements JobAreasServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getRoomList(int $jobId): Collection
    {
        return JobRoom::query()
            ->where('job_id', $jobId)
            ->get();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getRoom(int $jobId, int $roomId): JobRoom
    {
        return JobRoom::query()
            ->where('job_id', $jobId)
            ->findOrFail($roomId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException;
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function addRoom(JobRoomData $data, int $jobId): JobRoom
    {
        $this->throwExceptionIfJobIsClosed($jobId);

        $room         = new JobRoom($data->toArray());
        $room->job_id = $jobId;
        $room->save();

        return $room;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException;
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateRoom(JobRoomData $data, int $jobId, int $roomId): JobRoom
    {
        $this->throwExceptionIfJobIsClosed($jobId);

        $room = $this->getRoom($jobId, $roomId);
        $room->update($data->toArray());

        return $room;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException;
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteRoom(int $jobId, int $roomId): void
    {
        $this->throwExceptionIfJobIsClosed($jobId);

        JobRoom::destroy($roomId);
    }

    /**
     * @param int $jobId
     *
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException;
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function throwExceptionIfJobIsClosed(int $jobId): void
    {
        $job = $this->jobsService()
            ->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('No changes can be made to closed or cancelled job.');
        }
    }
}
