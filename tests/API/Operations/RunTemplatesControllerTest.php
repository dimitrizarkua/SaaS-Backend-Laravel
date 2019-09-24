<?php

namespace Tests\API\Operations;

use App\Components\Locations\Models\Location;
use App\Components\Operations\Models\JobRunTemplate;
use Tests\API\ApiTestCase;

/**
 * Class RunTemplatesControllerTest
 *
 * @package Tests\API\Operations
 * @group   jobs
 * @group   api
 */
class RunTemplatesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'operations.runs_templates.view', 'operations.runs_templates.manage',
    ];

    public function testListLocationTemplates()
    {
        $location = factory(Location::class)->create();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobRunTemplate::class, $count)->create([
            'location_id' => $location->id,
        ]);

        $url = action('Operations\RunTemplatesController@listLocationTemplates', [
            'location_id' => $location->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testViewTemplateSuccess()
    {
        $template = factory(JobRunTemplate::class)->create();

        $url = action('Operations\RunTemplatesController@show', [
            'template_id' => $template->id,
        ]);

        $data = $this->getJson($url)->assertStatus(200)->getData();
        self::assertEquals($data['id'], $template->id);
        self::assertEquals($data['name'], $template->name);
    }

    public function testViewTemplate404()
    {
        $url = action('Operations\RunTemplatesController@show', [
            'template_id' => $this->faker->randomNumber(),
        ]);

        $this->getJson($url)->assertStatus(404);
    }

    public function testCreateTemplateSuccess()
    {
        $location = factory(Location::class)->create();

        $data = [
            'location_id' => $location->id,
        ];

        $url  = action('Operations\RunTemplatesController@store');
        $data = $this->postJson($url, $data)->assertStatus(201)->getData();

        $reloaded = JobRunTemplate::findOrFail($data['id']);
        self::assertEquals($reloaded->location_id, $data['location_id']);
    }

    public function testCreateTemplateWOLocationFail()
    {
        $url = action('Operations\RunTemplatesController@store');

        $this->postJson($url, [])->assertStatus(422);
    }

    public function testUpdateTemplateSuccess()
    {
        $template = factory(JobRunTemplate::class)->create();

        $data = [
            'name' => $this->faker->word,
        ];

        $url = action('Operations\RunTemplatesController@update', [
            'template_id' => $template->id,
        ]);

        $data = $this->patchJson($url, $data)->assertStatus(200)->getData();

        $reloaded = JobRunTemplate::findOrFail($data['id']);
        self::assertEquals($reloaded->name, $data['name']);
    }

    public function testUpdateTemplate404()
    {
        $url = action('Operations\RunTemplatesController@update', [
            'template_id' => $this->faker->randomNumber(),
        ]);

        $this->patchJson($url)->assertStatus(404);
    }

    public function testDeleteTemplateSuccess()
    {
        $template = factory(JobRunTemplate::class)->create();

        $url = action('Operations\RunTemplatesController@destroy', [
            'template_id' => $template->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        self::assertNull(JobRunTemplate::find($template->id));
    }

    public function testDeleteTemplate404()
    {
        $url = action('Operations\RunTemplatesController@destroy', [
            'template_id' => $this->faker->randomNumber(),
        ]);

        $this->deleteJson($url)->assertStatus(404);
    }
}
