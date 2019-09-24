<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Events\JobAssignedToTeam;
use App\Components\Jobs\Events\JobAssignedToUser;
use App\Components\Jobs\Events\JobUnassignedFromTeam;
use App\Components\Jobs\Events\JobUnassignedFromUser;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobFollower;
use App\Components\Jobs\Models\JobTeam;
use App\Components\Jobs\Models\JobUser;
use App\Components\Teams\Models\Team;
use App\Components\Teams\Models\TeamMember;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobUsersServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   services
 */
class JobUsersServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobUsersServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobUsersServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testFollow()
    {
        $job      = $this->fakeJobWithStatus();
        $assigner = factory(User::class)->create();

        $this->service->follow($job->id, $assigner->id);

        JobFollower::query()
            ->where([
                'job_id'  => $job->id,
                'user_id' => $assigner->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->followers()->count());
    }

    public function testFailFollowClosedJob()
    {
        $job      = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $follower = factory(User::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->follow($job->id, $follower->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUnFollow()
    {
        $jobFollower = factory(JobFollower::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $this->service->unfollow($jobFollower->job_id, $jobFollower->user_id);

        self::expectException(ModelNotFoundException::class);
        JobFollower::query()
            ->where([
                'job_id'  => $jobFollower->job_id,
                'user_id' => $jobFollower->user_id,
            ])
            ->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testFailUnFollowClosedJob()
    {
        $job         = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobFollower = factory(JobFollower::class)->create([
            'job_id' => $job->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->unfollow($jobFollower->job_id, $jobFollower->user_id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDuplicateFollow()
    {
        $job      = $this->fakeJobWithStatus();
        $assigner = factory(User::class)->create();

        $this->service->follow($job->id, $assigner->id);
        self::expectExceptionMessage('This user is already follows specified job.');
        self::expectException(NotAllowedException::class);
        $this->service->follow($job->id, $assigner->id);
    }

    /**
     * @throws \Throwable
     */
    public function testAssignToUser()
    {
        Event::fake();
        $job      = $this->fakeJobWithStatus();
        $assigner = factory(User::class)->create();

        $this->service->assignToUser($job->id, $assigner->id);

        JobUser::query()
            ->where([
                'job_id'  => $job->id,
                'user_id' => $assigner->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->assignedUsers()->count());

        Event::assertDispatched(JobAssignedToUser::class, function (JobAssignedToUser $e) use ($job, $assigner) {
            return $e->targetModel->id === $job->id && $e->assignedUserId === $assigner->id;
        });
    }

    /**
     * @throws \Throwable
     */
    public function testFailAssignToUserForClosedJob()
    {
        Event::fake();

        $job      = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $assigner = factory(User::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->assignToUser($job->id, $assigner->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUnAssignFromUser()
    {
        Event::fake();
        $jobUser = factory(JobUser::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $this->service->unassignFromUser($jobUser->job_id, $jobUser->user_id);

        Event::assertDispatched(JobUnassignedFromUser::class, function ($e) use ($jobUser) {
            return $e->job->id === $jobUser->job_id && $e->userId === $jobUser->user_id;
        });

        self::expectException(ModelNotFoundException::class);
        JobUser::query()
            ->where([
                'job_id'  => $jobUser->job_id,
                'user_id' => $jobUser->user_id,
            ])
            ->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testFailUnAssignFromUserForClosedJob()
    {
        Event::fake();

        $job     = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobUser = factory(JobUser::class)->create([
            'job_id' => $job->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->unassignFromUser($jobUser->job_id, $jobUser->user_id);
    }

    public function testUserHasDirectAssignment()
    {
        /** @var JobUser $jobUser */
        $jobUser    = factory(JobUser::class)->create();
        $assignment = $this->service->isUserAssigned($jobUser->job_id, $jobUser->user_id);

        self::assertTrue($assignment);
    }

    public function testUserHasNoAssignment()
    {
        /** @var Job $job */
        $job = factory(Job::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $assignment = $this->service->isUserAssigned($job->id, $user->id);

        self::assertTrue(!$assignment);
    }

    public function testUserHasNoDirectAssignmentButHisTeamHas()
    {
        /** @var JobTeam $jobTeam */
        $jobTeam = factory(JobTeam::class)->create();
        /** @var TeamMember $teamUser */
        $teamUser   = factory(TeamMember::class)->create([
            'team_id' => $jobTeam->team_id,
        ]);
        $assignment = $this->service->isUserAssigned($jobTeam->job_id, $teamUser->user_id);

        self::assertTrue($assignment);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDuplicateUserAssignments()
    {
        $job      = $this->fakeJobWithStatus();
        $assigner = factory(User::class)->create();

        $this->service->assignToUser($job->id, $assigner->id);

        self::expectException(NotAllowedException::class);
        $this->service->assignToUser($job->id, $assigner->id);
    }

    /**
     * @throws \Throwable
     */
    public function testAssignToTeam()
    {
        Event::fake();

        $job  = $this->fakeJobWithStatus();
        $team = factory(Team::class)->create();

        $this->service->assignToTeam($job->id, $team->id);

        JobTeam::query()
            ->where([
                'job_id'  => $job->id,
                'team_id' => $team->id,
            ])
            ->firstOrFail();

        Event::assertDispatched(JobAssignedToTeam::class, function ($e) use ($job, $team) {
            return $e->job->id === $job->id && $e->teamId === $team->id;
        });

        self::assertEquals(1, $job->assignedTeams()->count());
    }

    /**
     * @throws \Throwable
     */
    public function testFailAssignToTeamForClosedJob()
    {
        Event::fake();

        $job  = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $team = factory(Team::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->assignToTeam($job->id, $team->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUnAssignFromTeam()
    {
        Event::fake();

        $jobTeam = factory(JobTeam::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $this->service->unassignFromTeam($jobTeam->job_id, $jobTeam->team_id);

        Event::assertDispatched(JobUnassignedFromTeam::class, function ($e) use ($jobTeam) {
            return $e->job->id === $jobTeam->job_id && $e->teamId === $jobTeam->team_id;
        });

        self::expectException(ModelNotFoundException::class);
        JobTeam::query()
            ->where([
                'job_id'  => $jobTeam->job_id,
                'team_id' => $jobTeam->team_id,
            ])
            ->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testFailUnAssignFromTeamForClosedJob()
    {
        Event::fake();

        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        $jobTeam = factory(JobTeam::class)->create([
            'job_id' => $job->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');

        $this->service->unassignFromTeam($jobTeam->job_id, $jobTeam->team_id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDuplicateTeamAssignments()
    {
        $job  = $this->fakeJobWithStatus();
        $team = factory(Team::class)->create();

        $this->service->assignToTeam($job->id, $team->id);

        self::expectExceptionMessage('This team is already assigned to specified job.');
        self::expectException(NotAllowedException::class);
        $this->service->assignToTeam($job->id, $team->id);
    }
}
