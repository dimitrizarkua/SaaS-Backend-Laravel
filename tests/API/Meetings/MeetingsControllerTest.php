<?php

namespace Tests\API\Meetings;

use App\Components\Meetings\Models\Meeting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class MeetingsControllerTest
 *
 * @package Tests\API\Meetings
 * @group   meetings
 * @group   api
 */
class MeetingsControllerTest extends ApiTestCase
{
    protected $permissions = ['meetings.delete', 'meetings.create', 'meetings.view'];

    public function testCreateRecord()
    {
        /** @var Meeting $instance */
        $instance = factory(Meeting::class)->create();

        $data = [
            'title'        => $instance->title,
            'scheduled_at' => $instance->scheduled_at,
        ];

        $url = action('Meetings\MeetingsController@store');

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(201)
            ->assertSeeData();

        $recordId = $response->getData()['id'];
        $instance = Meeting::findOrFail($recordId);

        self::assertEquals($this->user->id, $instance->user_id);
        self::assertEquals($data['title'], $instance->title);
        self::assertEquals($data['scheduled_at'], $instance->scheduled_at);
    }

    public function testGetOneRecord()
    {
        /** @var Meeting $instance */
        $instance = factory(Meeting::class)->create(['user_id' => $this->user->id,]);

        $url = action('Meetings\MeetingsController@show', ['id' => $instance->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($instance->id);
    }

    public function testNotFoundResponseWhenGettingNotExistingRecord()
    {
        $url      = action('Meetings\MeetingsController@show', ['id' => 0]);
        $response = $this->getJson($url);

        $response->assertStatus(404);
    }

    public function testDeleteRecord()
    {
        /** @var Meeting $instance */
        $instance = factory(Meeting::class)->create(['user_id' => $this->user->id,]);

        $url      = action('Meetings\MeetingsController@destroy', ['id' => $instance->id]);
        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        Meeting::findOrFail($instance->id);
    }

    public function testOthersCantDeleteSomeonesRecord()
    {
        /** @var Meeting $instance */
        $instance = factory(Meeting::class)->create();

        $url = action('Meetings\MeetingsController@destroy', ['id' => $instance->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(403);
    }
}
