<?php

namespace Tests\API\RBAC;

use App\Models\User;
use Tests\API\ApiTestCase;
use App\Components\RBAC\Models\Role;
use App\Components\RBAC\Models\RoleUser;

/**
 * Class UserRolesControllerTest
 *
 * @package Tests\API\RBAC
 */
class UserRolesControllerTest extends ApiTestCase
{
    protected $permissions = ['users.view', 'users.update'];

    public function testUserRoles()
    {
        $countOfRoles = 3;
        $user         = factory(User::class)->create();
        factory(Role::class, $countOfRoles)->create()
            ->each(function (Role $role) use ($user) {
                $role->users()->attach($user->id);
            });

        $url = action('RBAC\UserRolesController@getUserRoles', ['userId' => $user->id]);
        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonDataCount($countOfRoles);
    }

    public function testAttachRoleSuccess(): void
    {
        $user = factory(User::class)->create();
        /** @var Role $role */
        $role = factory(Role::class)->create();

        $requestData = [
            'roles' => [$role->id],
        ];

        $url = action('RBAC\UserRolesController@attachRole', ['userId' => $user->id]);

        $response = $this->postJson($url, $requestData);

        $response->assertStatus(200)
            ->assertJsonDataCount(1);

        $data = $response->getData()[0];
        self::assertEquals($role->id, $data['id']);
        self::assertEquals($role->name, $data['name']);
        self::assertEquals($role->description, $data['description']);
        self::assertEquals($role->display_name, $data['display_name']);

        $result = RoleUser::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        self::assertNotNull($result);
    }

    public function testAttachRoleCheckExceptionWhenUserAlreadyHasRole(): void
    {
        $user = factory(User::class)->create();
        /** @var Role $role */
        $oldRole = factory(Role::class)->create();
        $oldRole->users()->attach($user->id);

        $newRole = factory(Role::class)->create();

        $requestData = [
            'roles' => [$newRole->id],
        ];

        $url = action('RBAC\UserRolesController@attachRole', ['userId' => $user->id]);

        $response = $this->postJson($url, $requestData);

        $response->assertStatus(405)
            ->assertSee('The user already has a role.');
    }


    public function testAttachRoleCheckExceptionWhenTryToAttachSeveralRoles(): void
    {
        $user = factory(User::class)->create();
        /** @var Role $role */
        $roleIds = factory(Role::class)->create()->pluck('id')->toArray();

        $requestData = [
            'roles' => $roleIds,
        ];

        $url = action('RBAC\UserRolesController@attachRole', ['userId' => $user->id]);

        $response = $this->postJson($url, $requestData);

        $response->assertStatus(405)
            ->assertSee('Multiple user roles attaching is forbidden.');
    }

    public function testDetachRole(): void
    {
        $user = factory(User::class)->create();
        /** @var Role $role */
        $role = factory(Role::class)->create();
        $role->users()->attach($user->id);

        $requestData = [
            'roles' => [$role->id],
        ];

        $url = action('RBAC\UserRolesController@detachRoles', ['userId' => $user->id]);

        $response = $this->deleteJson($url, $requestData);

        $response->assertStatus(200)
            ->assertJsonDataCount(0);

        $result = RoleUser::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        self::assertNull($result);
    }

    public function testChangeRoleSuccess(): void
    {
        $user = factory(User::class)->create();
        /** @var Role $role */
        $oldRole = factory(Role::class)->create();
        $oldRole->users()->attach($user->id);

        $newRole = factory(Role::class)->create();

        $requestData = [
            'roles' => [$newRole->id],
        ];

        $url = action('RBAC\UserRolesController@changeRole', ['userId' => $user->id]);

        $response = $this->patchJson($url, $requestData);

        $response->assertStatus(200)
            ->assertJsonDataCount(1);

        $data = $response->getData()[0];
        self::assertEquals($newRole->id, $data['id']);
        self::assertEquals($newRole->name, $data['name']);
        self::assertEquals($newRole->description, $data['description']);
        self::assertEquals($newRole->display_name, $data['display_name']);
    }
}
