<?php

namespace Tests\API\Management;

use App\Components\Finance\Models\AccountType;
use App\Jobs\Management\SeederJob;
use Illuminate\Routing\Router;
use Tests\API\ApiTestCase;

/**
 * Class SeederControllerTest
 *
 * @package Tests\API\Management
 * @group   management
 */
class SeederControllerTest extends ApiTestCase
{
    protected $permissions = ['management.seed'];

    public function setUp()
    {
        parent::setUp();
        $this->models[] = AccountType::class;
    }

    public function testShouldReturnSuccessCode()
    {
        $data = [
            'class' => 'AccountTypeGroupsSeeder',
        ];

        $this->expectsJobs(SeederJob::class);

        $url = action('Management\SeederController@seed');
        $this->postJson($url, $data)
            ->assertStatus(200);
    }

    public function testErrorShouldBeReturned()
    {
        $data = [
            'class' => 'SomeNonExistingSeeder',
        ];

        $url = action('Management\SeederController@seed');
        $this->postJson($url, $data)
            ->assertStatus(404);
    }
}
