<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobAreasServiceInterface;
use App\Components\Jobs\Models\JobRoom;
use App\Components\AssessmentReports\Models\FlooringType;
use App\Components\Jobs\Models\VO\JobRoomData;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobAreasServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   site-survey
 * @group   jobs
 * @group   areas
 * @group   services
 */
class JobAreasServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var JobAreasServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(JobAreasServiceInterface::class);
    }

    public function testGetRoomList()
    {
        $count = $this->faker->numberBetween(1, 5);
        $job   = $this->fakeJobWithStatus();
        factory(JobRoom::class, $count)->create([
            'job_id' => $job->id,
        ]);

        $rooms = $this->service->getRoomList($job->id);

        self::assertCount($count, $rooms);
    }

    public function testGetRoom()
    {
        /** @var JobRoom $room */
        $room = factory(JobRoom::class)->create();

        $reloaded = $this->service->getRoom($room->job_id, $room->id);

        self::compareDataWithModel($reloaded->toArray(), $room);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testAddRoom()
    {
        $job = $this->fakeJobWithStatus();
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $data         = new JobRoomData([
            'flooring_type_id'   => $flooringType->id,
            'name'               => $this->faker->word,
            'total_sqm'          => $this->faker->randomFloat(2, 10, 50),
            'affected_sqm'       => $this->faker->randomFloat(2, 5, 25),
            'non_restorable_sqm' => $this->faker->randomFloat(2, 1, 10),
        ]);

        $createdJobRoom = $this->service->addRoom($data, $job->id);

        self::assertEquals($createdJobRoom->job_id, $job->id);
        self::compareDataWithModel($data->toArray(), $createdJobRoom);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToAddRoomToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $data         = new JobRoomData([
            'flooring_type_id'   => $flooringType->id,
            'name'               => $this->faker->word,
            'total_sqm'          => $this->faker->randomFloat(2, 10, 50),
            'affected_sqm'       => $this->faker->randomFloat(2, 5, 25),
            'non_restorable_sqm' => $this->faker->randomFloat(2, 1, 10),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->addRoom($data, $job->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testUpdateRoom()
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobRoom $jobRoom */
        $jobRoom = factory(JobRoom::class)->create([
            'job_id' => $job->id,
        ]);
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $data         = new JobRoomData([
            'flooring_type_id'   => $flooringType->id,
            'name'               => $this->faker->word,
            'total_sqm'          => $this->faker->randomFloat(2, 10, 50),
            'affected_sqm'       => $this->faker->randomFloat(2, 5, 25),
            'non_restorable_sqm' => $this->faker->randomFloat(2, 1, 10),
        ]);

        $updatedJobRoom = $this->service->updateRoom($data, $job->id, $jobRoom->id);

        self::compareDataWithModel($data->toArray(), $updatedJobRoom);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateRoomWhenJobIsClosed()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        /** @var JobRoom $jobRoom */
        $jobRoom = factory(JobRoom::class)->create([
            'job_id' => $job->id,
        ]);
        /** @var FlooringType $flooringType */
        $flooringType = factory(FlooringType::class)->create();
        $data         = new JobRoomData([
            'flooring_type_id'   => $flooringType->id,
            'name'               => $this->faker->word,
            'total_sqm'          => $this->faker->randomFloat(2, 10, 50),
            'affected_sqm'       => $this->faker->randomFloat(2, 5, 25),
            'non_restorable_sqm' => $this->faker->randomFloat(2, 1, 10),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->updateRoom($data, $job->id, $jobRoom->id);
    }

    public function testDeleteRoom()
    {
        $job     = $this->fakeJobWithStatus();
        $jobRoom = JobRoom::create([
            'job_id' => $job->id,
            'name'   => $this->faker->word,
        ]);

        $this->service->deleteRoom($job->id, $jobRoom->id);

        self::expectException(ModelNotFoundException::class);
        JobRoom::whereId($jobRoom->id)
            ->firstOrFail();
    }

    public function testFailToDeleteRoomFromClosedJob()
    {
        $job     = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobRoom = JobRoom::create([
            'job_id' => $job->id,
            'name'   => $this->faker->word,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->deleteRoom($job->id, $jobRoom->id);
    }
}
