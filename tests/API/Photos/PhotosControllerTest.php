<?php

namespace Tests\API\Photos;

use App\Components\Jobs\Models\Job;
use App\Components\Photos\Interfaces\PhotosServiceInterface;
use App\Components\Photos\Models\Photo;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\API\ApiTestCase;

/**
 * Class PhotosControllerTest
 *
 * @package Tests\API\Photos
 * @group   photos
 * @group   api
 *
 * @property \App\Components\Photos\Interfaces\PhotosServiceInterface service
 */
class PhotosControllerTest extends ApiTestCase
{
    protected $permissions = [
        'photos.view',
        'photos.create',
        'photos.update',
        'photos.delete',
    ];

    /** @var PhotosServiceInterface */
    private $service = null;

    public function setUp()
    {
        parent::setUp();

        $this->service = app()->make(PhotosServiceInterface::class);
    }

    public function testGetPhotoSuccess()
    {
        /** @var Photo $instance */
        $instance = factory(Photo::class)->create();
        $url      = action('Photos\PhotosController@show', ['photo_id' => $instance->id]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $data = $response->assertStatus(200)->assertSeeData()->getData();
        $this->service->deletePhoto($data['id']);

        self::assertEquals($instance->id, $data['id']);
        self::assertEquals($instance->file_name, $data['file_name']);
        self::assertEquals($instance->file_size, $data['file_size']);
        self::assertEquals($instance->mime_type, $data['mime_type']);
        self::assertEquals($instance->width, $data['width']);
        self::assertEquals($instance->height, $data['height']);
        self::assertEquals($instance->original_photo_id, $data['original_photo_id']);
        self::assertEquals($instance->created_at, Carbon::make($data['created_at']));
        self::assertEquals($instance->updated_at, Carbon::make($data['updated_at']));
        self::assertArrayHasKey('url', $data);
        self::assertArrayNotHasKey('storage_uid', $data);
    }

    public function testGetPhoto404()
    {
        $url = action('Photos\PhotosController@show', ['photo_id' => $this->faker->randomNumber()]);
        $this->getJson($url)->assertStatus(404);
    }

    public function testCreatePhotoSuccess()
    {
        /** @var Photo $instance */
        $url  = action('Photos\PhotosController@store');
        $file = $this->getFakeImage();

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, ['file' => $file]);

        $data = $response->assertStatus(201)->assertSeeData()->getData();
        $this->service->deletePhoto($data['id']);

        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('file_name', $data);
        self::assertArrayHasKey('file_size', $data);
        self::assertArrayHasKey('mime_type', $data);
        self::assertArrayHasKey('width', $data);
        self::assertArrayHasKey('height', $data);
        self::assertArrayHasKey('original_photo_id', $data);
        self::assertArrayHasKey('created_at', $data);
        self::assertArrayHasKey('updated_at', $data);
        self::assertArrayHasKey('url', $data);
        self::assertArrayNotHasKey('storage_uid', $data);
        self::assertEquals($file->getClientOriginalName(), $data['file_name']);
        self::assertEquals($file->getMimeType(), $data['mime_type']);
        self::assertEquals($file->getSize(), $data['file_size']);
    }

    public function testUpdatePhotoSuccess()
    {
        /** @var Photo $instance */
        $instance = factory(Photo::class)->create();
        $url      = action('Photos\PhotosController@reupload', ['photo_id' => $instance->id]);
        $file     = $this->getFakeImage();

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, ['file' => $file]);

        $data = $response->assertStatus(200)->assertSeeData()->getData();
        $this->service->deletePhoto($data['id']);

        self::assertEquals($instance->id, $data['id']);
        self::assertEquals($file->getClientOriginalName(), $data['file_name']);
        self::assertEquals($file->getSize(), $data['file_size']);
        self::assertEquals($file->getMimeType(), $data['mime_type']);
        self::assertArrayHasKey('width', $data);
        self::assertArrayHasKey('height', $data);
        self::assertArrayHasKey('original_photo_id', $data);
        self::assertArrayHasKey('created_at', $data);
        self::assertArrayHasKey('updated_at', $data);
        self::assertArrayHasKey('url', $data);
        self::assertArrayNotHasKey('storage_uid', $data);
    }

    public function testDownloadPhotoSuccess()
    {
        $file = $this->getFakeImage();
        $url  = action('Photos\PhotosController@store');
        $data = $this->postJson($url, ['file' => $file])->getData();

        $url = action('Photos\PhotosController@download', ['photo_id' => $data['id']]);

        $response = $this->get($url);
        $this->service->deletePhoto($data['id']);

        $response->assertStatus(200);
        $response->assertHeader('content-type', $data['mime_type']);
        $response->assertHeader('content-length', $data['file_size']);
        $response->assertHeader('content-disposition', 'inline; filename=' . $data['file_name']);
    }

    public function testDownloadMultiplePhotosSuccess()
    {
        $file = $this->getFakeImage();
        $url  = action('Photos\PhotosController@store');
        $data = $this->postJson($url, ['file' => $file])->getData();

        $url = action('Photos\PhotosController@downloadMultiple', ['photo_ids' => [$data['id']]]);

        $response = $this->get($url);
        $this->service->deletePhoto($data['id']);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');

        if ($response->baseResponse instanceof BinaryFileResponse) {
            // `BinaryFileResponse::deleteFileAfterSend` does not work in test environment
            // so we need to clean up files manually
            unlink($response->baseResponse->getFile());
        }
    }

    public function testDeletePhotoSuccess()
    {
        /** @var Photo $instance */
        $instance = factory(Photo::class)->create();
        $url      = action('Photos\PhotosController@destroy', ['photo_id' => $instance->id]);
        $this->deleteJson($url)->assertStatus(200);

        $reloaded = Photo::find($instance->id);
        self::assertNull($reloaded);
    }

    public function testDeleteThumbnailFail()
    {
        /** @var Photo $instance */
        $instance = factory(Photo::class)->create();

        /** @var Photo $thumbnail */
        $thumbnail = factory(Photo::class)->create([
            'original_photo_id' => $instance->id,
        ]);

        $url = action('Photos\PhotosController@destroy', ['photo_id' => $thumbnail->id]);
        $this->deleteJson($url)->assertStatus(405);
    }

    public function testDeleteAttachedPhotoFail()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();

        /** @var Job $job */
        $job = factory(Job::class)->create();
        $job->photos()->attach($photo->id);

        $url = action('Photos\PhotosController@destroy', ['photo_id' => $photo->id]);
        $this->deleteJson($url)->assertStatus(405);
    }

    /**
     * @param string|null $fileName
     *
     * @return \Illuminate\Http\Testing\File
     */
    private function getFakeImage(string $fileName = null)
    {
        if (!$fileName) {
            $fileName = $this->faker->word . $this->faker->randomElement(['.png', '.jpg']);
        }

        return UploadedFile::fake()->image($fileName);
    }
}
