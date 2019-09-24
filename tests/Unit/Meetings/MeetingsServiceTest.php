<?php

namespace Tests\Unit\Meetings;

use App\Components\Meetings\Interfaces\MeetingsServiceInterface;
use App\Components\Meetings\Models\Meeting;
use App\Components\Meetings\Models\MeetingData;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

/**
 * Class MeetingsServiceTest
 *
 * @package Tests\Unit\Meetings
 * @group   meetings
 * @group   meetings-service
 */
class MeetingsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Meetings\Interfaces\MeetingsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = Container::getInstance()
            ->make(MeetingsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testGetMeeting()
    {
        $meetingFromFactory    = factory(Meeting::class)->create();
        $meetingFromGetMeeting = $this->service->getMeeting($meetingFromFactory->id);

        self::assertInstanceOf(Meeting::class, $meetingFromGetMeeting);
        self::assertEquals($meetingFromFactory->title, $meetingFromGetMeeting->title);
        self::assertEquals($meetingFromFactory->user_id, $meetingFromGetMeeting->user_id);
        self::assertEquals($meetingFromFactory->scheduled_at, $meetingFromGetMeeting->scheduled_at);
        self::assertEquals($meetingFromFactory->created_at, $meetingFromGetMeeting->created_at);
    }

    public function testAddMeeting()
    {
        $meeting     = factory(Meeting::class)->make();
        $meetingData = new MeetingData([
            'title'        => $meeting->title,
            'user_id'      => $meeting->user_id,
            'scheduled_at' => $meeting->scheduled_at,
        ]);

        $result = $this->service->addMeeting($meetingData);

        self::assertInstanceOf(Meeting::class, $result);
        self::assertEquals($meetingData->getTitle(), $result->title);
        self::assertEquals($meetingData->getUserId(), $result->user_id);
        self::assertEquals($meetingData->getScheduledAt(), $result->scheduled_at);
    }

    public function testDeleteMeeting()
    {
        $meeting   = factory(Meeting::class)->create();
        $meetingId = $meeting->id;
        $this->service->deleteMeeting($meeting);

        self::expectException(ModelNotFoundException::class);
        Meeting::findOrFail($meetingId);
    }
}
