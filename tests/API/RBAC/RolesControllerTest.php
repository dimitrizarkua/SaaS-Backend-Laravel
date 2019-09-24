<?php

namespace Tests\API\RBAC;

use Tests\API\ApiTestCase;
use App\Components\RBAC\Models\Role;

/**
 * Class RolesControllerTest
 *
 * @package Tests\API\RBAC
 */
class RolesControllerTest extends ApiTestCase
{
    protected $permissions = ['roles.view', 'roles.delete', 'roles.create', 'roles.update'];

    public function testGetRoles()
    {
        $url      = action('RBAC\RolesController@index');
        $response = $this->json('GET', $url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonCount(1, 'data');
    }

    public function testGetOneRole()
    {
        $url      = action('RBAC\RolesController@show', ['role_id' => $this->testRole->id]);
        $response = $this->json('GET', $url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($this->testRole->name);
    }

    public function testGetOneRoleForNotExisting()
    {
        $response = $this->json('GET', '/v1/roles/' . $this->faker->randomDigit);
        $response->assertStatus(404);
    }

    public function testCreateRole()
    {
        $data = [
            'name'         => $this->faker->word,
            'display_name' => $this->faker->word,
            'description'  => $this->faker->word,
        ];

        $url      = action('RBAC\RolesController@store');
        $response = $this->json('POST', $url, $data);

        $response->assertStatus(201);

        $role = Role::where('name', $data['name'])->first();
        self::assertNotNull($role);
        self::assertEquals($data['display_name'], $role->display_name);
        self::assertEquals($data['description'], $role->description);
    }

    public function testValidationErrorWhenCreatingRole()
    {
        $url      = action('RBAC\RolesController@store');
        $response = $this->json('POST', $url, []);

        $response->assertStatus(422);
    }

    public function testUpdateRole()
    {
        $role = factory(Role::class)->create();

        $url      = action('RBAC\RolesController@update', ['role_id' => $role->id]);
        $response = $this->patchJson($url, ['name' => 'updated']);

        $response->assertStatus(200);
        $role = Role::find($role->id);
        self::assertEquals('updated', $role->name);
    }

    public function testDeleteRole()
    {
        $role   = factory(Role::class)->create();
        $roleId = $role->id;

        $url      = action('RBAC\RolesController@destroy', ['roleId' => $roleId]);
        $response = $this->json('DELETE', $url);

        $response->assertStatus(200);

        $reloaded = Role::find($roleId);
        self::assertNull($reloaded);
    }
}
