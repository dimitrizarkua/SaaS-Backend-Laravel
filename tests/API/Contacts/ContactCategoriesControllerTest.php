<?php

namespace Tests\API\Contacts;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\ContactCategory;
use Tests\API\ApiTestCase;

/**
 * Class ContactCategoriesControllerTest
 *
 * @package Tests\API\Contacts
 * @group   contacts
 * @group   api
 */
class ContactCategoriesControllerTest extends ApiTestCase
{
    protected $permissions = ['contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete'];

    public function testGetAllRecords()
    {
        $countOfRecords = $this->faker->numberBetween(1, 5);
        factory(ContactCategory::class, $countOfRecords)->create();

        $url = action('Contacts\ContactCategoriesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testGetOneRecord()
    {
        $category = factory(ContactCategory::class)->create();

        $url = action('Contacts\ContactCategoriesController@show', [
            'contact_category_id' => $category->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertEquals($data['id'], $category->id);
        self::assertEquals($data['name'], $category->name);
    }

    public function testAddContactCategory()
    {
        $url  = action('Contacts\ContactCategoriesController@store');
        $data = [
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(ContactCategoryTypes::values()),
        ];

        $response = $this->postJson($url, $data);
        $response->assertStatus(201)
            ->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = ContactCategory::find($responseData['id']);
        self::assertNotNull($reloaded);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['type'], $reloaded->type);
    }

    public function testUpdateContactCategory()
    {
        $category = factory(ContactCategory::class)->create();

        $url  = action('Contacts\ContactCategoriesController@update', [
            'category_id' => $category->id,
        ]);
        $data = [
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(ContactCategoryTypes::values()),
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(200)
            ->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = ContactCategory::find($responseData['id']);
        self::assertNotNull($reloaded);
        self::assertEquals($data['name'], $reloaded->name);
    }

    public function testDeleteContactCategory()
    {
        $category = factory(ContactCategory::class)->create();

        $url = action('Contacts\ContactCategoriesController@destroy', [
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        $reloaded = ContactCategory::find($category->id);
        self::assertNull($reloaded);
    }
}
