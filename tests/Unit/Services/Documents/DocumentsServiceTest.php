<?php

namespace Tests\Unit\Services\Documents;

use App\Components\Documents\Exceptions\InvalidAssociationException;
use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Documents\Models\Document;
use App\Components\Notes\Models\DocumentNote;
use App\Components\Notes\Models\Note;
use App\Components\Teams\Models\Team;
use App\Core\Utils\Curl;
use Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class DocumentsServiceTest
 *
 * @package Tests\Unit\Services\Documents
 */
class DocumentsServiceTest extends TestCase
{
    use DatabaseTransactions;

    const SMALL_FILE_SIZE_IN_KB = 1024;
    const BIG_FILE_SIZE_IN_KB   = 1024 * 50;

    /**
     * @var \App\Components\Documents\Services\DocumentsService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(DocumentsServiceInterface::class);
    }

    public function tearDown()
    {
        $storage = Storage::disk($this->service->getDiskName());

        array_map(
            function ($file) use ($storage) {
                $storage->delete($file);
            },
            $storage->allFiles()
        );

        unset($this->service);

        parent::tearDown();
    }

    public function testGetDiskName()
    {
        self::assertEquals('documents_local', $this->service->getDiskName());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateDocumentFromFile()
    {
        $file = UploadedFile::fake()->create('test.pdf', self::SMALL_FILE_SIZE_IN_KB);
        $document = $this->service->createDocumentFromFile($file);

        Storage::disk($this->service->getDiskName())->assertExists($document->storage_uid);

        self::assertEquals($file->getClientOriginalName(), $document->file_name);
        self::assertEquals($file->getSize(), $document->file_size);
        self::assertEquals($file->getMimeType(), $document->mime_type);
    }

    /**
     * @throws \Throwable
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCreateDocumentFromUrl()
    {
        $fileName = 'test.txt';
        $url = Storage::disk('public')->url($fileName);

        $mock = \Mockery::mock('alias:' . Curl::class);
        $mock->shouldReceive('downloadFile')
            ->once()
            ->withArgs(function ($targetUrl) use ($url) {
                return $targetUrl === $url;
            })
            ->andReturnUsing(function ($url, $tmpPath) use ($fileName) {
                File::put($tmpPath, $this->faker->paragraph);

                return true;
            });

        $fileName = $this->faker->uuid . '.' . $this->faker->fileExtension;
        $mimeType = 'text/plain';

        $document = $this->service->createDocumentFromUrl($url, $fileName, $mimeType);

        Storage::disk($this->service->getDiskName())->assertExists($document->storage_uid);

        self::assertEquals($fileName, $document->file_name);
        self::assertEquals($mimeType, $document->mime_type);
        self::assertNotNull($document->file_size);
        self::assertNotNull($document->file_hash);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateDocumentFrom50MbFileSize()
    {
        $file = UploadedFile::fake()->create('test.pdf', self::BIG_FILE_SIZE_IN_KB);
        $document = $this->service->createDocumentFromFile($file);

        Storage::disk($this->service->getDiskName())->assertExists($document->storage_uid);

        self::assertEquals($file->getSize(), $document->file_size);
    }

    /**
     * @throws \Throwable
     */
    public function testGetDocument()
    {
        $file = UploadedFile::fake()->create('test.pdf');
        $document = $this->service->createDocumentFromFile($file);
        $documentId = $this->service->getDocument($document->id)->id;

        self::assertEquals($document->id, $documentId);
    }

    public function testFailToGetDocument()
    {
        self::expectException(ModelNotFoundException::class);
        $this->service->getDocument(0);
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Throwable
     */
    public function testGetDocumentContents()
    {
        $file = UploadedFile::fake()->create('test.pdf');
        $expectedContent = $file->get();

        $document = $this->service->createDocumentFromFile($file);
        $content = $this->service->getDocumentContents($document->id);

        self::assertEquals($expectedContent, $content);
    }

    /**
     * @throws \Throwable
     */
    public function testGetDocumentContentsAsResponse()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $response = $this->service->getDocumentContentsAsResponse($document->id);

        self::assertEquals('text/plain', $response->headers->get('Content-Type'));
        self::assertEquals(
            "inline; filename={$file->getClientOriginalName()}",
            $response->headers->get('Content-Disposition')
        );
    }

    public function testFailToCheckLocalDriverNotSupportTemporaryUrl()
    {
        $document = factory(Document::class)->create();
        $tempUrl = $this->service->getDocumentTemporaryUrl($document->id);

        self::assertNull($tempUrl);
    }

    public function testDriverSupportTemporaryUrl()
    {
        $document = factory(Document::class)->create();
        $previousDiskName = $this->service->getDiskName();
        $this->service->setDiskName('mock');

        $this->mockStorage()->shouldReceive('temporaryUrl')->once()->andReturn('url');
        $tempUrl = $this->service->getDocumentTemporaryUrl($document->id);

        $this->service->setDiskName($previousDiskName);

        self::assertEquals($tempUrl, 'url');
    }

    private function mockStorage()
    {
        Storage::extend('mock', function () {
            return \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        });

        Config::set('filesystems.disks.mock', ['driver' => 'mock']);
        Config::set('filesystems.default', 'mock');

        return Storage::disk();
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateDocumentContentsFromFileWithoutUpdateFilename()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $document->file_name = 'UPDATED';
        $document->save();

        $updatedDocument = $this->service->updateDocumentContentsFromFile(
            $document->id,
            $file,
            false
        );

        self::assertEquals($document->file_name, $updatedDocument->file_name);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateDocumentContentsFromFileWithUpdateFilename()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $this->service->updateDocumentContentsFromFile(
            $document->id,
            $file,
            true
        );

        $updatedDocument = $this->service->getDocument($document->id);

        self::assertEquals($file->getClientOriginalName(), $updatedDocument->file_name);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateDocumentContentsFromFileWithUpdateFilename()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        /** @var Document $document */
        $document = $this->service->createDocumentFromFile($file);
        $document->file_name = 'UPDATED';
        $document->save();

        $updatedDocument = $this->service->updateDocumentContentsFromFile(
            $document->id,
            $file,
            true
        );

        self::assertEquals($file->getClientOriginalName(), $updatedDocument->file_name);
        self::assertNotEquals($document->file_name, $updatedDocument->file_name);
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Throwable
     */
    public function testDeleteDocument()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);

        $this->service->deleteDocument($document->id);
        Storage::disk($this->service->getDiskName())->assertExists($document->storage_uid);

        self::expectException(ModelNotFoundException::class);
        $this->service->getDocumentContents($document->id);
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Throwable
     */
    public function testForceDeleteDocument()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);

        $this->service->deleteDocument($document->id, true);

        Storage::disk($this->service->getDiskName())->assertMissing($document->storage_uid);

        self::expectException(FileNotFoundException::class);
        $this->service->getDocumentContentByUid($document->storage_uid);

        self::expectException(ModelNotFoundException::class);
        $this->service->getDocument($document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testLinkDocumentTo()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $note = factory(Note::class)->create();

        $this->service->linkDocumentTo($document->id, $note);

        /** @var Note $note */
        self::assertEquals($note->documents()->first()->id, $document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToLinkDocumentTo()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $modelNotContainsDocumentRelation = factory(Team::class)->create();

        self::expectException(InvalidAssociationException::class);
        $this->service->linkDocumentTo($document->id, $modelNotContainsDocumentRelation);

        self::assertEquals($modelNotContainsDocumentRelation->documents()->first()->id, $document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUnlinkDocumentFrom()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $note = factory(Note::class)->create();

        $this->service->linkDocumentTo($document->id, $note);
        $this->service->unlinkDocumentFrom($document->id, $note);

        self::expectException(ModelNotFoundException::class);

        DocumentNote::query()->where([
            'document_id' => $document->id,
            'note_id'     => $note->id,
        ])->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUnlinkDocumentFrom()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $modelNotContainsDocumentRelation = factory(Team::class)->create();

        self::expectException(InvalidAssociationException::class);
        $this->service->unlinkDocumentFrom($document->id, $modelNotContainsDocumentRelation);
    }

    /**
     * @doesNotPerformAssertions
     *
     * @throws \Throwable
     */
    public function testFailToLinkDocumentToSilent()
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $document = $this->service->createDocumentFromFile($file);
        $modelNotContainsDocumentRelation = factory(Team::class)->create();

        $this->service->linkDocumentTo($document->id, $modelNotContainsDocumentRelation, true);
    }
}
