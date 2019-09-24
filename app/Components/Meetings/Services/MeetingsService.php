<?php

namespace App\Components\Meetings\Services;

use App\Components\Meetings\Interfaces\MeetingsServiceInterface;
use App\Components\Meetings\Models\Meeting;
use App\Components\Meetings\Models\MeetingData;

/**
 * Class MeetingsService
 *
 * @package App\Components\Meetings\Services
 */
class MeetingsService implements MeetingsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMeeting(int $meetingId): Meeting
    {
        return Meeting::findOrFail($meetingId);
    }

    /**
     * {@inheritdoc}
     */
    public function addMeeting(MeetingData $meetingData): Meeting
    {
        $meeting = Meeting::create($meetingData->toArray());
        $meeting->saveOrFail();

        return $meeting;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMeeting(Meeting $meeting): void
    {
        $meeting->delete();
    }
}
