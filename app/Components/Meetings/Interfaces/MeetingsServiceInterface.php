<?php

namespace App\Components\Meetings\Interfaces;

use App\Components\Meetings\Models\Meeting;
use App\Components\Meetings\Models\MeetingData;

/**
 * Interface MeetingsServiceInterface
 *
 * @package App\Components\Meetings\Interfaces
 */
interface MeetingsServiceInterface
{
    /**
     * Returns meeting by id.
     *
     * @param int $meetingId Meeting id.
     *
     * @return Meeting
     */
    public function getMeeting(int $meetingId): Meeting;

    /**
     * Creates new meeting.
     *
     * @param MeetingData $meetingData Input data set for meeting creation.
     *
     * @return Meeting
     */
    public function addMeeting(MeetingData $meetingData): Meeting;

    /**
     * Removes meeting.
     *
     * @param Meeting $meeting .
     */
    public function deleteMeeting(Meeting $meeting): void;
}
