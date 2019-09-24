<?php

namespace Tests\Unit\Microsoft;

use App\Components\Office365\Models\UserResource;
use Tests\TestCase;

/**
 * Class UserResourceTest
 *
 * @package Tests\Unit\Microsoft
 * @group   microsoft
 */
class UserResourceTest extends TestCase
{
    public function testGetEmailmethodWithMailField()
    {
        $email = $this->faker->email;

        $resource = new UserResource(['mail' => $email]);
        self::assertEquals($email, $resource->getEmail());
    }

    public function testGetEmailmethodWithPrincipalNameField()
    {
        $email = $this->faker->email;

        $resource = new UserResource(['userPrincipalName' => $email]);
        self::assertEquals($email, $resource->getEmail());
    }

    public function testGetEmailmethodWithBothFields()
    {
        $email = $this->faker->email;

        $resource = new UserResource([
            'mail'              => $email,
            'userPrincipalName' => $this->faker->email,
        ]);
        self::assertEquals($email, $resource->getEmail());
    }

    public function testGetEmailDomainMethod()
    {
        $resource = new UserResource(['mail' => 'test@test.com']);
        $domain   = $resource->getEmailDomain();
        self::assertEquals('test.com', $domain);
    }

    public function testGetEmailMethodShouldThrowException()
    {
        $resource = new UserResource();
        $this->expectException(\InvalidArgumentException::class);
        $resource->getEmailDomain();
    }
}
