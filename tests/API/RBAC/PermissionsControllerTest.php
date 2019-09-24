<?php

namespace Tests\API\RBAC;

use Tests\API\ApiTestCase;
use App\Components\RBAC\Models\Role;
use App\Components\RBAC\Models\PermissionRole;

/**
 * Class PermissionsControllerTest
 *
 * @package Tests\API\RBAC
 */
class PermissionsControllerTest extends ApiTestCase
{
    protected $permissions = ['permissions.view', 'roles.view', 'roles.update'];

    public function testGetPermissions()
    {
        $permissionsCount = $this->getAllPermissions()
            ->count();

        $url      = action('RBAC\PermissionsController@index');
        $response = $this->json('GET', $url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($permissionsCount);
    }

    public function testGetOnePermission()
    {
        /** @var \App\Components\RBAC\Models\Permission $permission */
        $permission = $this->getAllPermissions()->first();

        if (null === $permission) {
            $this->markTestSkipped('There is no permission in the app');
        }

        $url      = action('RBAC\PermissionsController@show', ['permission' => $permission->getName()]);
        $response = $this->json('GET', $url);

        $response->assertStatus(200);
        $retrievedPermission = $response->getData();

        self::assertEquals($permission->getName(), $retrievedPermission['name']);
        self::assertEquals($permission->getDescription(), $retrievedPermission['description']);
        self::assertEquals($permission->getDisplayName(), $retrievedPermission['display_name']);
    }

    public function testGetRolePermissions()
    {
        $permissionsCount = 3;

        $role = factory(Role::class)->create();
        factory(PermissionRole::class, $permissionsCount)->create([
            'role_id' => $role->id,
        ]);

        $url = action('RBAC\PermissionsController@getRolePermissions', [
            'roleId' => $role->id,
        ]);

        $response = $this->json('GET', $url);

        $response->assertStatus(200)
            ->assertJsonDataCount($permissionsCount);
    }

    public function testAttachPermissionsToRole()
    {
        $role       = factory(Role::class)->create();
        $permission = $this->getAllPermissions()->random();
        if (null === $permission) {
            $this->markTestSkipped('There is no permission in the app');
        }

        $url = action('RBAC\PermissionsController@attachPermissionsToRole', [
            'roleId' => $role->id,
        ]);

        $requestData = [
            'permissions' => [$permission->getName()],
        ];

        $response = $this->json('POST', $url, $requestData);

        $response->assertStatus(200)
            ->assertJsonDataCount(1);

        $result = PermissionRole::where('role_id', $role->id)
            ->where('permission', $permission->getName())
            ->first();

        self::assertNotNull($result);
    }

    public function testDetachPermission()
    {
        $role = factory(Role::class)->create();
        /** @var \App\Components\RBAC\Models\Permission $permission */
        $permission = $this->getAllPermissions()->first();
        factory(PermissionRole::class)->create([
            'role_id'    => $role->id,
            'permission' => $permission->getName(),
        ]);

        $requestData = [
            'permissions' => [$permission->getName()],
        ];

        $url = action('RBAC\PermissionsController@detachPermissionsFromRole', [
            'role_id' => $role->id,
        ]);

        $response = $this->json('DELETE', $url, $requestData);
        $response->assertStatus(200);

        $result = PermissionRole::where('role_id', $role->id)
            ->where('permission', $permission->getName())
            ->first();

        self::assertNull($result);
    }

    /**
     * @return \App\Components\RBAC\Models\Permission[]|\Illuminate\Support\Collection
     */
    private function getAllPermissions()
    {
        return $this->RBACService->getPermissionDataProvider()
            ->getAllPermissions();
    }
}
