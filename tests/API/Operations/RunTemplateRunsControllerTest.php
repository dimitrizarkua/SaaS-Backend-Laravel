<?php

namespace Tests\API\Operations;

use App\Components\Operations\Models\JobRunTemplate;
use App\Components\Operations\Models\JobRunTemplateRun;
use App\Components\Operations\Models\Vehicle;
use App\Models\User;
use Tests\API\ApiTestCase;

/**
 * Class RunTemplateRunsControllerTest
 *
 * @package Tests\API\Operations
 * @group   jobs
 * @group   api
 */
class RunTemplateRunsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'operations.runs_templates.view', 'operations.runs_templates.manage',
    ];

    public function testListTemplateRuns()
    {
        $template = factory(JobRunTemplate::class)->create();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobRunTemplateRun::class, $count)->create([
            'job_run_template_id' => $template->id,
        ]);

        $url = action('Operations\RunTemplateRunsController@listTemplateRuns', [
            'template_id' => $template->id,
        ]);

        $this->getJson($url)->assertStatus(200)->assertJsonCount($count, 'data');
    }

    public function testViewTemplateRunSuccess()
    {
        $templateRun = factory(JobRunTemplateRun::class)->create();

        $url = action('Operations\RunTemplateRunsController@viewTemplateRun', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
        ]);

        $data = $this->getJson($url)->assertStatus(200)->getData();
        self::assertEquals($templateRun->id, $data['id']);
        self::assertEquals($templateRun->name, $data['name']);
        self::assertArrayHasKey('assigned_users', $data);
        self::assertArrayHasKey('assigned_vehicles', $data);
    }

    public function testViewTemplateRun404()
    {
        $url = action('Operations\RunTemplateRunsController@viewTemplateRun', [
            'template_id' => $this->faker->randomNumber(),
            'run_id'      => $this->faker->randomNumber(),
        ]);

        $this->getJson($url)->assertStatus(404);
    }

    public function testAddTemplateRunSuccess()
    {
        $template = factory(JobRunTemplate::class)->create();

        $data = [
            'name' => $this->faker->word,
        ];

        $url = action('Operations\RunTemplateRunsController@addTemplateRun', [
            'template_id' => $template->id,
        ]);

        $data = $this->postJson($url, $data)->assertStatus(201)->getData();

        JobRunTemplateRun::findOrFail($data['id']);
    }

    public function testUpdateTemplateRunSuccess()
    {
        $templateRun = factory(JobRunTemplateRun::class)->create();

        $data = [
            'name' => $this->faker->word,
        ];

        $url = action('Operations\RunTemplateRunsController@updateTemplateRun', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
        ]);

        $this->patchJson($url, $data)->assertStatus(200);

        $reloaded = JobRunTemplateRun::findOrFail($templateRun->id);
        self::assertEquals($data['name'], $reloaded->name);
    }

    public function testUpdateTemplateRun404()
    {
        $data = [
            'name' => $this->faker->word,
        ];

        $url = action('Operations\RunTemplateRunsController@updateTemplateRun', [
            'template_id' => $this->faker->randomNumber(),
            'run_id'      => $this->faker->randomNumber(),
        ]);

        $this->patchJson($url, $data)->assertStatus(404);
    }

    public function testDeleteTemplateRunSuccess()
    {
        $templateRun = factory(JobRunTemplateRun::class)->create();

        $url = action('Operations\RunTemplateRunsController@deleteTemplateRun', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::assertNull(JobRunTemplateRun::find($templateRun->id));
    }

    public function testDeleteTemplateRun404()
    {
        $url = action('Operations\RunTemplateRunsController@deleteTemplateRun', [
            'template_id' => $this->faker->randomNumber(),
            'run_id'      => $this->faker->randomNumber(),
        ]);

        $this->deleteJson($url)->assertStatus(404);
    }

    public function testAssignUserSuccess()
    {
        $user        = factory(User::class)->create();
        $templateRun = factory(JobRunTemplateRun::class)->create();

        $url = action('Operations\RunTemplateRunsController@assignUser', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
            'user_id'     => $user->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $reloaded        = JobRunTemplateRun::findOrFail($templateRun->id);
        $assignedUserIds = $reloaded->assignedUsers->pluck('id');

        self::assertCount(1, $assignedUserIds);
        self::assertContains($user->id, $assignedUserIds);
    }

    public function testAssignUserTwiceFail()
    {
        $user        = factory(User::class)->create();
        $templateRun = factory(JobRunTemplateRun::class)->create();
        $templateRun->assignedUsers()->attach($user);

        $url = action('Operations\RunTemplateRunsController@assignUser', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
            'user_id'     => $user->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignUserSuccess()
    {
        $user = factory(User::class)->create();

        $templateRun = factory(JobRunTemplateRun::class)->create();
        $templateRun->assignedUsers()->attach($user->id);

        self::assertCount(1, $templateRun->assignedUsers);

        $url = action('Operations\RunTemplateRunsController@unassignUser', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
            'user_id'     => $user->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobRunTemplateRun::findOrFail($templateRun->id);
        self::assertCount(0, $reloaded->assignedUsers);
    }

    public function testAssignVehicleSuccess()
    {
        $vehicle     = factory(Vehicle::class)->create();
        $templateRun = factory(JobRunTemplateRun::class)->create();

        $url = action('Operations\RunTemplateRunsController@assignVehicle', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
            'vehicle_id'  => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(200);

        $reloaded           = JobRunTemplateRun::findOrFail($templateRun->id);
        $assignedVehicleIds = $reloaded->assignedVehicles->pluck('id');

        self::assertCount(1, $assignedVehicleIds);
        self::assertContains($vehicle->id, $assignedVehicleIds);
    }

    public function testAssignVehicleTwiceFail()
    {
        $vehicle     = factory(Vehicle::class)->create();
        $templateRun = factory(JobRunTemplateRun::class)->create();
        $templateRun->assignedVehicles()->attach($vehicle);

        $url = action('Operations\RunTemplateRunsController@assignVehicle', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
            'vehicle_id'  => $vehicle->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignVehicleSuccess()
    {
        $vehicle = factory(Vehicle::class)->create();

        $templateRun = factory(JobRunTemplateRun::class)->create();
        $templateRun->assignedVehicles()->attach($vehicle->id);

        self::assertCount(1, $templateRun->assignedVehicles);

        $url = action('Operations\RunTemplateRunsController@unassignVehicle', [
            'template_id' => $templateRun->job_run_template_id,
            'run_id'      => $templateRun->id,
            'user_id'     => $vehicle->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = JobRunTemplateRun::findOrFail($templateRun->id);
        self::assertCount(0, $reloaded->assignedVehicles);
    }
}
