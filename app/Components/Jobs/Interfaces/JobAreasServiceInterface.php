<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Models\JobRoom;
use App\Components\Jobs\Models\VO\JobRoomData;
use Illuminate\Support\Collection;

/**
 * Interface JobAreasServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobAreasServiceInterface
{
    /**
     * Returns a list of rooms attached to a job.
     *
     * @param int $jobId Job identifier.
     *
     * @return Collection
     */
    public function getRoomList(int $jobId): Collection;

    /**
     * Returns specified job room.
     *
     * @param int $jobId  Job identifier.
     * @param int $roomId JobRoom identifier.
     *
     * @return JobRoom
     */
    public function getRoom(int $jobId, int $roomId): JobRoom;

    /**
     * Creates and attaches a room to a job.
     *
     * @param JobRoomData $data  JobRoom data.
     * @param int         $jobId Job identifier.
     *
     * @return JobRoom
     */
    public function addRoom(JobRoomData $data, int $jobId): JobRoom;

    /**
     * Updates specified job room.
     *
     * @param JobRoomData $data   Room data.
     * @param int         $jobId  Job identifier.
     * @param int         $roomId JobRoom identifier.
     *
     * @return JobRoom
     */
    public function updateRoom(JobRoomData $data, int $jobId, int $roomId): JobRoom;

    /**
     * Removes specified job room.
     *
     * @param int $jobId  Job identifier.
     * @param int $roomId JobRoom identifier.
     */
    public function deleteRoom(int $jobId, int $roomId): void;
}
