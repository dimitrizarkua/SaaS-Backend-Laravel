<?php

namespace Tests\API;

use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Components\RBAC\Models\Role;
use App\Models\User;
use Illuminate\Container\Container;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Class ApiTestCase
 *
 * @method \Tests\API\TestResponse json($method, $uri, array $data = [], array $headers = [])
 * @method \Tests\API\TestResponse postJson($uri, array $data = [], array $headers = [])
 * @method \Tests\API\TestResponse deleteJson($uri, array $data = [], array $headers = [])
 * @method \Tests\API\TestResponse getJson($uri, array $headers = [])
 * @method \Tests\API\TestResponse putJson($uri, array $data = [], array $headers = [])
 * @method \Tests\API\TestResponse patchJson($uri, array $data = [], array $headers = [])
 *
 * @package Tests\API
 */
class ApiTestCase extends TestCase
{
    /**
     * The list of permission that should have being set for user to make call to testing API endpoint.
     *
     * @var string[]
     */
    protected $permissions = [];

    /**
     * @var User
     */
    protected $user;
    /**
     * @var Role
     */
    protected $testRole;

    /**
     * @var \App\Components\RBAC\Interfaces\RBACServiceInterface
     */
    protected $RBACService;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Passport::actingAs($this->user, ['create-servers']);
        $this->RBACService = Container::getInstance()->make(RBACServiceInterface::class);

        $this->testRole = Role::create(['name' => $this->faker->name]);
        $this->RBACService->getUsersService()->attachRoles($this->user->id, [$this->testRole->id]);
        $this->RBACService->getRolesService()->attachPermissions($this->testRole->id, $this->permissions);
    }

    /**
     * Create the test response instance from the given response.
     *
     * @param  \Illuminate\Http\Response $response
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function createTestResponse($response)
    {
        return TestResponse::fromBaseResponse($response);
    }
}
