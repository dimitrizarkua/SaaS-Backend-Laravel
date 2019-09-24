<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\JobNotesTemplate;
use Tests\API\ApiTestCase;

/**
 * Class JobNotesTemplatesControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobNotesTemplatesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.update',
    ];

    public function testListJobNotesTemplates()
    {
        $count = $this->faker->numberBetween(1, 5);

        factory(JobNotesTemplate::class, $count)->create();

        $url = action('Jobs\JobNotesTemplatesController@index');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($count, 'data');
    }

    public function testListOnlyActive()
    {
        $count = $this->faker->numberBetween(1, 5);

        factory(JobNotesTemplate::class, $count)->create([
            'active' => true,
        ]);
        factory(JobNotesTemplate::class, $count)->create([
            'active' => false,
        ]);

        $url = action('Jobs\JobNotesTemplatesController@index', ['active' => 'true']);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($count, 'data');

        $data = $response->getData();
        foreach ($data as $template) {
            self::assertTrue($template['active']);
        }
    }

    public function testListOnlyInactive()
    {
        $count = $this->faker->numberBetween(1, 5);

        factory(JobNotesTemplate::class, $count)->create([
            'active' => true,
        ]);
        factory(JobNotesTemplate::class, $count)->create([
            'active' => false,
        ]);

        $url = action('Jobs\JobNotesTemplatesController@index', ['active' => 'false']);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($count, 'data');

        $data = $response->getData();
        foreach ($data as $template) {
            self::assertFalse($template['active']);
        }
    }

    public function testAddJobNotesTemplate()
    {
        $url  = action('Jobs\JobNotesTemplatesController@store');
        $data = [
            'name' => $this->faker->word,
            'body' => $this->faker->sentence,
        ];

        $response = $this->postJson($url, $data);
        $response->assertStatus(201)
            ->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = JobNotesTemplate::find($responseData['id']);
        self::assertNotNull($reloaded);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['body'], $reloaded->body);
        self::assertTrue($reloaded->active);
    }

    public function testAddAndStripTags()
    {
        $url  = action('Jobs\JobNotesTemplatesController@store');
        $data = [
            'name' => $this->faker->word,
            'body' => $this->faker->randomHtml(),
        ];

        $response = $this->postJson($url, $data);
        $response->assertStatus(201)
            ->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = JobNotesTemplate::find($responseData['id']);
        $strippedBody = strip_tags($reloaded->body, '<b><strong><i><u><ul><li><p><span><br><em>');
        self::assertEquals($reloaded->body, $strippedBody);
    }

    public function testUpdateJobNotesTemplate()
    {
        $template = factory(JobNotesTemplate::class)->create();

        $url  = action('Jobs\JobNotesTemplatesController@update', [
            'message_template_id' => $template->id,
        ]);
        $data = [
            'name'   => $this->faker->unique()->words(3, true),
            'body'   => $this->faker->sentence,
            'active' => $this->faker->boolean,
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(200)
            ->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = JobNotesTemplate::find($responseData['id']);
        self::assertNotNull($reloaded);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['body'], $reloaded->body);
        self::assertEquals($data['active'], $reloaded->active);
    }

    public function testDeleteJobNotesTemplate()
    {
        $template = factory(JobNotesTemplate::class)->create();

        $url = action('Jobs\JobNotesTemplatesController@destroy', [
            'message_template_id' => $template->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        $reloaded = JobNotesTemplate::find($template->id);
        self::assertNull($reloaded);
    }
}
