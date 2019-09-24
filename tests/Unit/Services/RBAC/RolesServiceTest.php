<?php

namespace Tests\Unit\Services\RBAC;

use App\Components\RBAC\Exceptions\InvalidArgumentException;
use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use App\Components\RBAC\Interfaces\RoleServiceInterface;
use App\Components\RBAC\Models\Role;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Class RolesServiceTest
 *
 * @package Tests\Unit\Services\RBAC
 */
class RolesServiceTest extends TestCase
{
    protected const TEST_ROLE_NAME           = 'DefinedTestRole';
    protected const TEST_UNDEFINED_ROLE_NAME = 'UndefinedTestRole';
    protected const ROLE_VIEW                = 'roles.view';
    protected const ROLE_UPDATE              = 'roles.update';

    public $models = [Role::class];
    /**
     * @var Role
     */
    protected $testRole;

    /**
     * @var \App\Components\RBAC\Interfaces\RoleServiceInterface
     */
    protected $roleService;
    /**
     * @var \App\Components\RBAC\Interfaces\PermissionDataProviderInterface
     */
    protected $permissionDataProvider;

    public function setUp()
    {
        parent::setUp();

        $this->roleService            = Container::getInstance()->make(RoleServiceInterface::class);
        $this->permissionDataProvider = Container::getInstance()->make(PermissionDataProviderInterface::class);
        $this->testRole               = Role::create(['name' => self::TEST_ROLE_NAME]);
    }

    public function tearDown()
    {
        unset(
            $this->testRole,
            $this->roleService,
            $this->permissionDataProvider
        );

        parent::tearDown();
    }

    public function testCreateRole()
    {
        $name        = $this->faker->word;
        $description = $this->faker->word;
        $displayName = $this->faker->word;

        $role = $this->roleService->createRole($name, $description, $displayName);

        self::assertNotNull($role);
        self::assertEquals($name, $role->name);
        self::assertEquals($description, $role->description);
        self::assertEquals($displayName, $role->display_name);
    }

    public function testIsExist()
    {
        self::assertTrue($this->roleService->isExists(self::TEST_ROLE_NAME));
        self::assertFalse($this->roleService->isExists(self::TEST_UNDEFINED_ROLE_NAME));
    }

    public function testAttachPermissions()
    {
        $attachedPermission    = $this->permissionDataProvider->getPermissionInstance(self::ROLE_VIEW);
        $notAttachedPermission = $this->permissionDataProvider->getPermissionInstance(self::ROLE_UPDATE);

        $this->roleService->attachPermissions($this->testRole->id, [$attachedPermission]);

        $rolePermissions = $this->roleService->getPermissions($this->testRole->id);
        self::assertEquals($rolePermissions, Collection::make([$attachedPermission]));
        self::assertNotEquals($rolePermissions, Collection::make([$notAttachedPermission]));
    }

    public function testFailToAttachDuplicatePermissions()
    {
        $attachedPermission = $this->permissionDataProvider->getPermissionInstance(self::ROLE_VIEW);

        $this->roleService->attachPermissions($this->testRole->id, [$attachedPermission]);

        self::expectException(InvalidArgumentException::class);
        $this->roleService->attachPermissions($this->testRole->id, [$attachedPermission]);
    }

    public function testDetachPermissions()
    {
        $attachedPermission = $this->permissionDataProvider->getPermissionInstance(self::ROLE_VIEW);
        $this->roleService->attachPermissions($this->testRole->id, [$attachedPermission]);

        $this->roleService->detachPermissions($this->testRole->id, [$attachedPermission]);

        $rolePermissions = $this->roleService->getPermissions($this->testRole->id);
        self::assertNotEquals($rolePermissions, Collection::make([$attachedPermission]));
    }

    public function testHasPermission()
    {
        $attachedPermission    = $this->permissionDataProvider->getPermissionInstance(self::ROLE_VIEW);
        $notAttachedPermission = $this->permissionDataProvider->getPermissionInstance(self::ROLE_UPDATE);

        $this->roleService->attachPermissions($this->testRole->id, [$attachedPermission]);

        self::assertTrue($this->roleService->hasPermission($this->testRole->id, $attachedPermission));
        self::assertTrue($this->roleService->hasPermission($this->testRole->id, self::ROLE_VIEW));
        self::assertFalse($this->roleService->hasPermission($this->testRole->id, $notAttachedPermission));
        self::assertFalse($this->roleService->hasPermission($this->testRole->id, self::ROLE_UPDATE));
    }

    public function testGetPermission()
    {
        $attachedPermission = $this->permissionDataProvider->getPermissionInstance(self::ROLE_VIEW);
        $this->roleService->attachPermissions($this->testRole->id, [$attachedPermission]);

        $rolePermissions = $this->roleService->getPermissions($this->testRole->id);

        self::assertEquals($rolePermissions, Collection::make([$attachedPermission]));
    }
}
