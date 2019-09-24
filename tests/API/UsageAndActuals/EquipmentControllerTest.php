<?php

namespace Tests\API\UsageAndActuals;

use App\Components\Locations\Models\Location;
use App\Components\Notes\Models\Note;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\EquipmentNote;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class EquipmentControllerTest
 *
 * @package Tests\API\UsageAndActuals
 * @group   api
 * @group   equipment
 * @group   usage-and-actuals
 */
class EquipmentControllerTest extends ApiTestCase
{
    protected $permissions = [
        'equipment.view',
        'management.equipment',
        'equipment.notes.edit',
    ];

    public function setUp()
    {
        parent::setUp();

        $models       = [
            Note::class,
            EquipmentCategory::class,
            Equipment::class,
            Location::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(Equipment::class, $count)->create();
        $url = action('UsageAndActuals\EquipmentController@index');

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($count);
    }

    public function testCreateMethod()
    {
        $data = [
            'barcode'               => $this->faker->sentence(2),
            'equipment_category_id' => factory(EquipmentCategory::class)->create()->id,
            'location_id'           => null,
            'make'                  => $this->faker->word,
            'model'                 => $this->faker->word,
            'serial_number'         => $this->faker->word,
            'last_test_tag_at'      => $this->faker->date('Y-m-d\TH:i:s\Z'),
        ];
        $url  = action('UsageAndActuals\EquipmentController@store');

        $response = $this->postJson($url, $data)
            ->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = Equipment::findOrFail($modelId);

        self::assertEquals($model->barcode, $data['barcode']);
        self::assertEquals($model->equipment_category_id, $data['equipment_category_id']);
        self::assertNull($model->location_id);
        self::assertEquals($model->make, $data['make']);
        self::assertEquals($model->model, $data['model']);
        self::assertEquals($model->serial_number, $data['serial_number']);
        self::assertEquals($model->last_test_tag_at, new Carbon($data['last_test_tag_at']));
    }

    public function testCreateMethodReturnsValidationErrorWhenWrongRequest()
    {
        $data = [
            'barcode'               => $this->faker->randomNumber(),
            'equipment_category_id' => null,
            'make'                  => $this->faker->randomNumber(),
            'model'                 => $this->faker->randomNumber(),
            'serial_number'         => $this->faker->randomNumber(),
            'last_test_tag_at'      => $this->faker->date('Y-m-d'),
        ];
        $url  = action('UsageAndActuals\EquipmentController@store');

        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testShowMethod()
    {
        /** @var Equipment $model */
        $model = factory(Equipment::class)->create();
        $url   = action('UsageAndActuals\EquipmentController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200);
        $data     = $response->getData();

        self::assertEquals($model->barcode, $data['barcode']);
        self::assertEquals($model->equipment_category_id, $data['equipment_category_id']);
        self::assertEquals($model->location_id, $data['location_id']);
        self::assertEquals($model->make, $data['make']);
        self::assertEquals($model->model, $data['model']);
        self::assertEquals($model->serial_number, $data['serial_number']);
        self::assertEquals($model->last_test_tag_at, new Carbon($data['last_test_tag_at']));
    }

    public function testUpdateMethod()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location->id);
        $data = [
            'barcode'               => $this->faker->sentence(2),
            'equipment_category_id' => factory(EquipmentCategory::class)->create()->id,
            'location_id'           => $location->id,
            'make'                  => $this->faker->word,
            'model'                 => $this->faker->word,
            'serial_number'         => $this->faker->word,
            'last_test_tag_at'      => $this->faker->date('Y-m-d\TH:i:s\Z'),
        ];
        $url  = action('UsageAndActuals\EquipmentController@update', [
            'id' => $equipment->id,
        ]);

        $response = $this->patchJson($url, $data)
            ->assertStatus(200);

        $modelId = $response->getData('id');
        $model   = Equipment::findOrFail($modelId);

        self::assertEquals($model->barcode, $data['barcode']);
        self::assertEquals($model->equipment_category_id, $data['equipment_category_id']);
        self::assertEquals($model->location_id, $data['location_id']);
        self::assertEquals($model->make, $data['make']);
        self::assertEquals($model->model, $data['model']);
        self::assertEquals($model->serial_number, $data['serial_number']);
        self::assertEquals($model->last_test_tag_at, new Carbon($data['last_test_tag_at']));
    }

    public function testUpdateMethodReturnsValidationErrorWhenWrongRequest()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        $data      = [
            'barcode'               => $this->faker->randomNumber(),
            'equipment_category_id' => null,
            'make'                  => $this->faker->randomNumber(),
            'model'                 => $this->faker->randomNumber(),
            'serial_number'         => $this->faker->randomNumber(),
            'last_test_tag_at'      => $this->faker->date('Y-m-d'),
        ];
        $url       = action('UsageAndActuals\EquipmentController@update', [
            'id' => $equipment->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(422);
    }

    public function testDestroyMethod()
    {
        /** @var Equipment $model */
        $model = factory(Equipment::class)->create();
        $url   = action('UsageAndActuals\EquipmentController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::assertNull(Equipment::find($model->id));
    }

    public function testGetEquipmentNotes()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        $count     = $this->faker->numberBetween(1, 5);
        for ($i = 0; $i < $count; $i++) {
            /** @var Note $note */
            $note = factory(Note::class)->create();
            EquipmentNote::create([
                'equipment_id' => $equipment->id,
                'note_id'      => $note->id,
            ]);
        }
        $url = action('UsageAndActuals\EquipmentController@getNotes', [
            'id' => $equipment->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount($count, 'data');
    }

    public function testAttachNoteToEquipment()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id' => $this->user->id,
        ]);
        $url  = action('UsageAndActuals\EquipmentController@attachNote', [
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(200);

        EquipmentNote::query()->where([
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ])->firstOrFail();
    }

    public function testFailToAttachNoteToEquipmentOwnedByOtherUser()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create();
        $url  = action('UsageAndActuals\EquipmentController@attachNote', [
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(403)
            ->assertSee('You are not authorized to perform this action.');
    }

    public function testFailToAttachNoteToEquipmentWhenAlreadyAttached()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id' => $this->user->id,
        ]);
        EquipmentNote::create([
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ]);
        $url = action('UsageAndActuals\EquipmentController@attachNote', [
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(405)
            ->assertSee('This note is already attached to the equipment.');
    }

    public function testDetachNoteFromEquipment()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id' => $this->user->id,
        ]);
        $equipment->notes()->attach($note->id);
        $url = action('UsageAndActuals\EquipmentController@detachNote', [
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        EquipmentNote::query()->where([
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ])->firstOrFail();
    }

    public function testFailToDetachNoteFromEquipmentOwnedByOtherUser()
    {
        /** @var Equipment $equipment */
        $equipment = factory(Equipment::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create();
        $equipment->notes()->attach($note->id);
        $url = action('UsageAndActuals\EquipmentController@detachNote', [
            'equipment_id' => $equipment->id,
            'note_id'      => $note->id,
        ]);

        $this->postJson($url)
            ->assertStatus(403)
            ->assertSee('You are not authorized to perform this action.');
    }
}
