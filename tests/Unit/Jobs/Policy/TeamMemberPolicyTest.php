<?php

namespace Tests\Unit\Jobs\Policy;

use App\Components\Jobs\Policies\TeamMemberPolicy;
use App\Components\Teams\Models\Team;
use App\Models\User;
use Tests\TestCase;

/**
 * Class TeamMemberPolicyTest
 *
 * @package Tests\Unit\Jobs\Policy
 * @group   policy
 * @group   jobs
 */
class TeamMemberPolicyTest extends TestCase
{
    /**
     * @var \App\Components\Jobs\Policies\TeamMemberPolicy
     */
    private $policy;

    public function setUp()
    {
        parent::setUp();
        $this->policy = new TeamMemberPolicy();
    }

    public function testIsMemberOfShouldReturnTrue()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Team $team */
        $team = factory(Team::class)->create();
        $team->users()->attach($user);

        $result = $this->policy->isMemberOf($user, $team);
        self::assertTrue($result);
    }

    public function testIsMemberOfShouldReturnFalse()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Team $team */
        $team = factory(Team::class)->create();

        $result = $this->policy->isMemberOf($user, $team);
        self::assertFalse($result);
    }
}
