<?php

namespace Tests\API\Tags;

use App\Components\Tags\Enums\TagTypes;
use App\Components\Tags\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class TagsControllerTest
 *
 * @package Tests\API\Tags
 * @group   tags
 * @group   api
 */
class TagsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'tags.view',
        'tags.delete',
        'tags.create',
        'tags.update',
    ];

    public function testGetOneRecord()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        $url = action('Tags\TagsController@show', ['tag_id' => $tag->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->json('GET', $url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($tag->name);
    }

    public function testNotFoundResponseWhenGettingNotExistingRecord()
    {
        $url = action('Tags\TagsController@show', ['tag_id' => 0]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(404);
    }

    public function testCreateRecord()
    {
        $data = [
            'name'     => $this->faker->word,
            'type'     => $this->faker->randomElement(TagTypes::values()),
            'is_alert' => $this->faker->boolean,
            'color'    => $this->faker->numberBetween(0, 16777215),
        ];

        $url = action('Tags\TagsController@store');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(201);

        $recordId = $response->getData()['id'];

        $tag = Tag::findOrFail($recordId);
        self::assertEquals($data['name'], $tag->name);
        self::assertEquals($data['type'], $tag->type);
        self::assertEquals($data['is_alert'], $tag->is_alert);
        self::assertEquals($data['color'], $tag->color);
    }

    public function testValidationErrorWhenCreatingRecord()
    {
        $url = action('Tags\TagsController@store');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, []);

        $response->assertStatus(422);
    }

    public function testValidationErrorWhenCreatingNotUniqueTag()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        $data = [
            'name'     => $tag->name,
            'type'     => $tag->type,
            'is_alert' => $this->faker->boolean,
        ];

        $url = action('Tags\TagsController@store');
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(422);
    }

    public function testUpdateRecord()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        $url  = action('Tags\TagsController@update', ['tag_id' => $tag->id]);
        $data = [
            'name'     => $this->faker->unique()->sentence(2),
            'is_alert' => $this->faker->boolean,
            'color'    => $this->faker->numberBetween(0, 16777215),
        ];
        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);

        $response->assertStatus(200);

        $tag = Tag::findOrFail($tag->id);
        self::assertEquals($data['name'], $tag->name);
        self::assertEquals($data['is_alert'], $tag->is_alert);
        self::assertEquals($data['color'], $tag->color);
    }

    public function testValidationErrorWhenUpdateNotUnique()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        /** @var Tag $conflicting */
        $conflicting = factory(Tag::class)->create();

        $data = [
            'name' => $conflicting->name,
        ];

        $url = action('Tags\TagsController@update', ['tag_id' => $tag->id]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);
        $response->assertStatus(422);
    }

    public function testDeleteRecord()
    {
        /** @var Tag $tag */
        $tag = factory(Tag::class)->create();

        $url = action('Tags\TagsController@destroy', ['tag_id' => $tag->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        Tag::findOrFail($tag->id);
    }
}
