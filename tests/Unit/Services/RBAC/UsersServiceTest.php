<?php

namespace Tests\Unit\Services\RBAC;

use App\Components\RBAC\Exceptions\InvalidArgumentException;
use App\Components\RBAC\Interfaces\PermissionDataProviderInterface;
use App\Components\RBAC\Interfaces\RoleServiceInterface;
use App\Components\RBAC\Interfaces\UsersServiceInterface;
use App\Components\RBAC\Models\Role;
use App\Components\RBAC\Models\RoleUser;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Class UsersServiceTest
 *
 * @package Tests\Unit\Services\RBAC
 */
class UsersServiceTest extends TestCase
{
    protected const TEST_ROLE_NAME         = 'DefinedTestRole';
    protected const ANOTHER_TEST_ROLE_NAME = 'AnotherDefinedTestRole';
    protected const ROLE_VIEW              = 'roles.view';
    protected const ROLE_UPDATE            = 'roles.update';

    /**
     * @var Role
     */
    protected $testRole;
    /**
     * @var Role
     */
    protected $anotherTestRole;
    /**
     * @var User
     */
    protected $user;
    /**
     * @var \App\Components\RBAC\Interfaces\RoleServiceInterface
     */
    protected $roleService;
    /**
     * @var \App\Components\RBAC\Interfaces\UsersServiceInterface
     */
    protected $userService;
    /**
     * @var \App\Components\RBAC\Interfaces\PermissionDataProviderInterface
     */
    protected $permissionDataProvider;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        Passport::actingAs($this->user, ['create-servers']);

        $this->roleService            = Container::getInstance()->make(RoleServiceInterface::class);
        $this->userService            = Container::getInstance()->make(UsersServiceInterface::class);
        $this->permissionDataProvider = Container::getInstance()->make(PermissionDataProviderInterface::class);
        $this->testRole               = Role::create(['name' => self::TEST_ROLE_NAME]);
        $this->anotherTestRole        = Role::create(['name' => self::ANOTHER_TEST_ROLE_NAME]);
    }

    public function tearDown()
    {
        unset(
            $this->user,
            $this->testRole,
            $this->anotherTestRole,
            $this->roleService,
            $this->userService,
            $this->permissionDataProvider
        );

        parent::tearDown();
    }

    public function testAttachRoles()
    {
        $this->userService->attachRoles($this->user->id, [$this->testRole]);
        $this->userService->attachRoles($this->user->id, [$this->anotherTestRole->id]);

        $userRole        = RoleUser::where('role_id', $this->testRole->id)
            ->where('user_id', $this->user->id)
            ->first();
        $anotherUserRole = RoleUser::where('role_id', $this->anotherTestRole->id)
            ->where('user_id', $this->user->id)
            ->first();
        self::assertEquals($userRole->user_id, $this->user->id);
        self::assertEquals($userRole->role_id, $this->testRole->id);
        self::assertEquals($anotherUserRole->user_id, $this->user->id);
        self::assertEquals($anotherUserRole->role_id, $this->anotherTestRole->id);
    }

    public function testChangeRoles()
    {
        $this->userService->attachRoles($this->user->id, [$this->testRole]);
        $this->userService->changeRole($this->user->id, [$this->anotherTestRole->id]);

        $roles = User::findOrFail($this->user->id)
            ->roles()
            ->get();

        self::assertCount(1, $roles);
        self::assertEquals($roles->first()->id, $this->anotherTestRole->id);
    }

    public function testFailToAttachDuplicateRoles()
    {
        $this->userService->attachRoles($this->user->id, [$this->testRole]);
        $this->userService->attachRoles($this->user->id, [$this->anotherTestRole->id]);

        self::expectException(InvalidArgumentException::class);
        $this->userService->attachRoles($this->user->id, [$this->testRole]);
        self::expectException(InvalidArgumentException::class);
        $this->userService->attachRoles($this->user->id, [$this->anotherTestRole->id]);
    }

    public function testDetachRoles()
    {
        $this->userService->attachRoles($this->user->id, [$this->testRole]);
        $this->userService->attachRoles($this->user->id, [$this->anotherTestRole->id]);

        $this->userService->detachRoles($this->user->id, [$this->testRole]);
        $this->userService->detachRoles($this->user->id, [$this->anotherTestRole->id]);

        $userRole        = RoleUser::where('role_id', $this->testRole->id)
            ->where('user_id', $this->user->id)
            ->first();
        $anotherUserRole = RoleUser::where('role_id', $this->anotherTestRole->id)
            ->where('user_id', $this->user->id)
            ->first();
        self::assertNull($userRole);
        self::assertNull($anotherUserRole);
    }

    public function testHasRole()
    {
        $this->userService->attachRoles($this->user->id, [$this->testRole]);

        self::assertTrue($this->userService->hasRole($this->user->id, $this->testRole));
        self::assertFalse($this->userService->hasRole($this->user->id, $this->anotherTestRole));
    }

    public function testHasPermission()
    {
        $attachedPermission    = $this->permissionDataProvider->getPermissionInstance(self::ROLE_VIEW);
        $notAttachedPermission = $this->permissionDataProvider->getPermissionInstance(self::ROLE_UPDATE);
        $this->roleService->attachPermissions($this->testRole->id, [$attachedPermission]);
        $this->userService->attachRoles($this->user->id, [$this->testRole]);

        self::assertTrue($this->userService->hasPermission($this->user->id, $attachedPermission));
        self::assertFalse($this->userService->hasPermission($this->user->id, $notAttachedPermission));
        self::assertTrue($this->userService->hasPermission($this->user->id, self::ROLE_VIEW));
        self::assertFalse($this->userService->hasPermission($this->user->id, self::ROLE_UPDATE));
    }

    public function testGetRoles()
    {
        $this->userService->attachRoles($this->user->id, [$this->testRole]);
        $roles = Role::whereHas('users', function (Builder $query) {
            $query->where('id', $this->user->id);
        })->get();

        self::assertEquals($this->userService->getRoles($this->user->id), $roles);
    }
}
