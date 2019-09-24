<?php

namespace Tests\Unit\Notifications\Policies;

use App\Components\Notifications\Policies\UserNotificationPolicy;
use App\Components\Notifications\Models\UserNotification;
use App\Models\User;
use Tests\TestCase;

/**
 * Class UserNotificationPolicyTest
 *
 * @package Tests\Unit\Notifications\Policy
 * @group   policy
 * @group   jobs
 * @group   notification
 */
class UserNotificationPolicyTest extends TestCase
{
    /**
     * @var \App\Components\Notifications\Policies\UserNotificationPolicy
     */
    private $policy;

    public function setUp()
    {
        parent::setUp();
        $this->policy = new UserNotificationPolicy();
    }

    public function testRead()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var UserNotification $userNotification */
        $userNotification = factory(UserNotification::class)->create([
            'user_id' => $user->id,
        ]);

        $result = $this->policy->isOwner($user, $userNotification);
        self::assertTrue($result);
    }

    public function testFailToRead()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var UserNotification $userNotification */
        $userNotification = factory(UserNotification::class)->create();

        $result = $this->policy->isOwner($user, $userNotification);
        self::assertFalse($result);
    }
}
