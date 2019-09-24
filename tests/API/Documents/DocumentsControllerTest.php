<?php

namespace Tests\API\Documents;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Documents\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\API\ApiTestCase;

/**
 * Class DocumentsControllerTest
 *
 * @package Tests\API\Documents
 * @group   documents
 * @group   api
 */
class DocumentsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'documents.create',
        'documents.view',
        'documents.download',
        'documents.delete',
    ];

    /** @var \App\Components\Documents\Interfaces\DocumentsServiceInterface */
    protected $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(DocumentsServiceInterface::class);

        Storage::fake($this->service->getDiskName());
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    public function testCreateDocument()
    {
        $url = action('Documents\DocumentsController@store');

        $file = UploadedFile::fake()->create('document.pdf', $this->faker->randomNumber(3));

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, ['file' => $file]);

        $response->assertStatus(201);
        $response->assertSeeData();

        $document = Document::find($response->json('data.id'));

        self::assertNotNull($document);

        Storage::disk($this->service->getDiskName())
            ->assertExists($document->storage_uid);

        self::assertEquals($file->getClientOriginalName(), $document->file_name);
        self::assertEquals($file->getSize(), $document->file_size);
        self::assertEquals($file->getMimeType(), $document->mime_type);
    }

    public function testGetDocumentInfo()
    {
        $file     = UploadedFile::fake()->create('document.pdf', $this->faker->randomNumber(3));
        $document = $this->service->createDocumentFromFile($file);

        $url = action('Documents\DocumentsController@show', ['id' => $document->id]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200);
        $response->assertSeeData();

        $document = Document::find($response->json('data.id'));

        self::assertEquals($file->getClientOriginalName(), $document->file_name);
        self::assertEquals($file->getSize(), $document->file_size);
        self::assertEquals($file->getMimeType(), $document->mime_type);
    }

    public function testDownloadDocument()
    {
        $file     = UploadedFile::fake()->create('plain_text.txt', $this->faker->randomNumber(3));
        $document = $this->service->createDocumentFromFile($file);

        $url = action('Documents\DocumentsController@download', ['id' => $document->id]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->get($url);

        $response->assertStatus(200);

        self::assertEquals('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertEquals(
            "inline; filename={$file->getClientOriginalName()}",
            $response->headers->get('Content-Disposition')
        );
    }

    public function testDeleteDocument()
    {
        $file     = UploadedFile::fake()->create('document.pdf', $this->faker->randomNumber(3));
        $document = $this->service->createDocumentFromFile($file);

        $url = action('Documents\DocumentsController@destroy', ['id' => $document->id]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        $document = Document::withTrashed()->find($document->id);
        self::assertTrue($document->trashed());
    }
}
