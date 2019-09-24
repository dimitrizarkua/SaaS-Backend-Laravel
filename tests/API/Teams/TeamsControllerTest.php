<?php

namespace Tests\API\Teams;

use App\Components\Teams\Events\UserAssignedToTeam;
use App\Components\Teams\Events\UserUnassignedFromTeam;
use App\Components\Teams\Models\Team;
use App\Components\Teams\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\API\ApiTestCase;

/**
 * Class TeamsControllerTest
 *
 * @package Tests\API\Teams
 * @group   teams
 * @group   api
 */
class TeamsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'teams.create',
        'teams.view',
        'teams.update',
        'teams.delete',
        'teams.modify_members',
    ];

    public function testCreateRecord()
    {
        $expectedName = $this->faker->word;
        $data         = [
            'name' => $expectedName,
        ];

        $url = action('Teams\TeamsController@store');

        $response = $this->postJson($url, $data);
        /** @var \Tests\API\TestResponse $response */
        $response->assertStatus(HttpResponse::HTTP_CREATED)
            ->assertSeeData();

        $recordId = $response->getData()['id'];
        $instance = Team::findOrFail($recordId);

        self::assertEquals($expectedName, $instance->name);
    }

    public function testGetAllRecords()
    {
        $instances = factory(Team::class, 3)->create();
        $url       = action('Teams\TeamsController@index');
        $response  = $this->getJson($url);
        /** @var \Tests\API\TestResponse $response */
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertSeeData()
            ->assertSee($instances[$this->faker->numberBetween(0, 2)]->id);

        self::assertEquals(count($instances), count($response->getData()));
    }

    public function testUpdateRecord()
    {
        $instanceBefore = factory(Team::class)->create();
        $url            = action('Teams\TeamsController@update', ['id' => $instanceBefore->id]);
        $expectedName   = 'UPDATED';
        $data           = ['name' => $expectedName];
        $response       = $this->patchJson($url, $data);
        /** @var \Tests\API\TestResponse $response */
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertSeeData()
            ->assertSee($instanceBefore->id);

        self::assertEquals($instanceBefore->id, $response->getData()['id']);
        self::assertEquals($expectedName, $response->getData()['name']);
    }

    public function testDeleteRecord()
    {
        $instance = factory(Team::class)->create();
        $url      = action('Teams\TeamsController@destroy', ['id' => $instance->id]);
        $response = $this->deleteJson($url);
        /** @var \Tests\API\TestResponse $response */
        $response->assertStatus(HttpResponse::HTTP_OK);

        self::expectException(ModelNotFoundException::class);
        Team::findOrFail($instance->id);
    }

    public function testAddUserToTeam()
    {
        Event::fake();

        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Team $team */
        $team = factory(Team::class)->create();

        $url = action('Teams\TeamsController@addUser', [
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(HttpResponse::HTTP_OK);

        TeamMember::query()->where([
            'team_id' => $team->id,
            'user_id' => $user->id,
        ])->firstOrFail();

        Event::dispatched(UserAssignedToTeam::class, function ($e) use ($team, $user) {
            return $e->team->id === $team->id && $e->userId === $user->id;
        });
    }

    public function testDeleteUserFromTeam()
    {
        Event::fake();

        /** @var TeamMember $teamMember */
        $teamMember = factory(TeamMember::class)->create();

        $url = action('Teams\TeamsController@deleteUser', [
            'team_id' => $teamMember->team_id,
            'user_id' => $teamMember->user_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(HttpResponse::HTTP_OK);

        Event::dispatched(UserUnassignedFromTeam::class, function ($e) use ($teamMember) {
            return $e->team->id === $teamMember->team_id && $e->userId === $teamMember->user_id;
        });

        self::expectException(ModelNotFoundException::class);
        TeamMember::query()->where([
            'team_id' => $teamMember->team_id,
            'user_id' => $teamMember->user_id,
        ])->firstOrFail();
    }

    public function testGetUserListByTeam()
    {
        $teamMember = factory(TeamMember::class)->create();

        $url = action('Teams\TeamsController@getMembers', [
            'team_id' => $teamMember->team_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertSeeData();

        self::assertNotEmpty($response->getData());
        $userId = $response->getData()[0]['id'];
        self::assertEquals($teamMember->user_id, $userId);
    }

    public function testTeamListListByUser()
    {
        $teamMember = factory(TeamMember::class)->create(['user_id' => $this->user->id]);

        $url = action('Users\UserProfileController@getTeams');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertSeeData();

        self::assertNotEmpty($response->getData());
        $teamId = $response->getData()[0]['id'];
        self::assertEquals($teamMember->team_id, $teamId);
    }
}
