<?php

namespace Tests\Unit\Teams;

use App\Components\Locations\Exceptions\NotAllowedException;
use App\Components\Teams\Events\UserAssignedToTeam;
use App\Components\Teams\Events\UserUnassignedFromTeam;
use App\Components\Teams\Interfaces\TeamsServiceInterface;
use App\Components\Teams\Models\Team;
use App\Components\Teams\Models\TeamMember;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class TeamsServiceTest
 *
 * @package Tests\Unit\Teams
 * @group   teams
 * @group   teams-service
 */
class TeamsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Teams\Interfaces\TeamsServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()
            ->make(TeamsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testAddUser()
    {
        Event::fake();

        /** @var Team $team */
        $team = factory(Team::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->service->addUser($team->id, $user->id);

        /** @var TeamMember $teamMember */
        $teamMember = TeamMember::query()->where([
            'team_id' => $team->id,
            'user_id' => $user->id,
        ])->firstOrFail();

        self::assertEquals($team->id, $teamMember->team_id);
        self::assertEquals($user->id, $teamMember->user_id);
        Event::dispatched(UserAssignedToTeam::class, function ($e) use ($team, $user) {
            return $e->team->id === $team->id && $e->userId === $user->id;
        });
    }

    public function testAddOneUserTwice()
    {
        /** @var Team $team */
        $team = factory(Team::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->service->addUser($team->id, $user->id);

        self::expectException(NotAllowedException::class);
        $this->service->addUser($team->id, $user->id);
    }

    public function testRemoveUser()
    {
        Event::fake();

        /** @var Team $team */
        $team = factory(Team::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $teamMember = factory(TeamMember::class)->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);

        $this->service->removeUser($team->id, $user->id);

        Event::dispatched(UserUnassignedFromTeam::class, function ($e) use ($teamMember) {
            return $e->team->id === $teamMember->team_id && $e->userId === $teamMember->user_id;
        });

        self::expectException(ModelNotFoundException::class);

        TeamMember::query()->where([
            'team_id' => $team->id,
            'user_id' => $user->id,
        ])->firstOrFail();
    }
}
