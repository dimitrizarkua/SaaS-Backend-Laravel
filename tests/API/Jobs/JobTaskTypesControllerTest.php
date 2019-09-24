<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\JobTaskType;

/**
 * Class JobTaskTypesControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobTaskTypesControllerTest extends JobTestCase
{
    protected $permissions = [
        'management.system.settings',
    ];

    public function testListJobTaskTypes()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(JobTaskType::class, $count)->create();

        $url = action('Jobs\JobTaskTypesController@index');
        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testViewJobTaskTypeSuccess()
    {
        $jobTaskType = factory(JobTaskType::class)->create();

        $url = action('Jobs\JobTaskTypesController@show', [
            'jobtasktype_id' => $jobTaskType->id,
        ]);

        $response = $this->getJson($url);

        $data = $response
            ->assertStatus(200)
            ->assertSeeData()
            ->getData();

        self::assertEquals($jobTaskType->id, $data['id']);
        self::assertEquals($jobTaskType->name, $data['name']);
        self::assertEquals($jobTaskType->can_be_scheduled, $data['can_be_scheduled']);
        self::assertEquals($jobTaskType->default_duration_minutes, $data['default_duration_minutes']);
        self::assertEquals($jobTaskType->kpi_hours, $data['kpi_hours']);
        self::assertEquals($jobTaskType->kpi_include_afterhours, $data['kpi_include_afterhours']);
        self::assertEquals($jobTaskType->color, $data['color']);
    }

    public function testViewJobTaskType404()
    {
        $url = action('Jobs\JobTaskTypesController@show', [
            'job_task_type_id' => $this->faker->randomNumber(),
        ]);
        $this->getJson($url)->assertStatus(404);
    }

    public function testAddJobTaskTypeSuccess()
    {
        $url = action('Jobs\JobTaskTypesController@store');

        $request = [
            'name'                     => $this->faker->word,
            'can_be_scheduled'         => $this->faker->boolean,
            'allow_edit_due_date'      => $this->faker->boolean,
            'default_duration_minutes' => $this->faker->numberBetween(1, 1440),
            'kpi_hours'                => $this->faker->numberBetween(1, 24),
            'kpi_include_afterhours'   => $this->faker->boolean,
            'color'                    => $this->faker->randomNumber(),
        ];

        $response = $this->postJson($url, $request);

        $data = $response
            ->assertStatus(201)
            ->assertSeeData()
            ->getData();

        $reloaded = JobTaskType::findOrFail($data['id']);

        foreach ($request as $field => $value) {
            $attributeValue = $reloaded->getAttribute($field);
            self::assertEquals($attributeValue, $value);
        }
    }

    public function testUpdateJobTaskTypeSuccess()
    {
        $jobTaskType = factory(JobTaskType::class)->create();

        $url = action('Jobs\JobTaskTypesController@update', [
            'job_task_type_id' => $jobTaskType->id,
        ]);

        $request = [
            'name'                     => $this->faker->unique()->word,
            'can_be_scheduled'         => $this->faker->boolean,
            'default_duration_minutes' => $this->faker->numberBetween(1, 1440),
            'kpi_hours'                => $this->faker->numberBetween(1, 24),
            'kpi_include_afterhours'   => $this->faker->boolean,
            'color'                    => $this->faker->randomNumber(),
        ];

        $response = $this->patchJson($url, $request);

        $data = $response
            ->assertStatus(200)
            ->assertSeeData()
            ->getData();

        $reloaded = JobTaskType::findOrFail($data['id']);

        foreach ($request as $field => $value) {
            $attributeValue = $reloaded->getAttribute($field);
            self::assertEquals($attributeValue, $value);
        }
    }

    public function testDeleteJobTaskTypeSuccess()
    {
        $jobTaskType = factory(JobTaskType::class)->create();

        $url = action('Jobs\JobTaskTypesController@destroy', [
            'job_task_type_id' => $jobTaskType->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::assertNull(JobTaskType::find($jobTaskType->id));
    }
}
