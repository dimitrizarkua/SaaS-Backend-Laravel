<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Photos\Models\Photo;

/**
 * Class JobPhotosControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobPhotosControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.update',
    ];

    public function testListJobPhotosSuccess()
    {
        $job = $this->fakeJobWithStatus();

        $photos = factory(Photo::class, $this->faker->numberBetween(1, 5))->create();
        foreach ($photos as $photo) {
            $job->photos()->attach($photo->id);
        }

        $url = action('Jobs\JobPhotosController@listJobPhotos', ['job_id' => $job->id]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonCount(count($photos), 'data');
    }

    public function testAttachPhotoToJobSuccess()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();

        $job = $this->fakeJobWithStatus();
        self::assertCount(0, $job->photos);

        $url = action('Jobs\JobPhotosController@attachPhoto', [
            'job_id'   => $job->id,
            'photo_id' => $photo->id,
        ]);
        $this->postJson($url)->assertStatus(200);

        $reloaded = Job::findOrFail($job->id);
        self::assertCount(1, $reloaded->photos);
    }

    public function testFailAttachPhotoToClosedJob()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();
        $job   = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        $url = action('Jobs\JobPhotosController@attachPhoto', [
            'job_id'   => $job->id,
            'photo_id' => $photo->id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testAttachPhotoTwiceFail()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();

        $job = $this->fakeJobWithStatus();
        $job->photos()->attach($photo->id);

        $url = action('Jobs\JobPhotosController@attachPhoto', [
            'job_id'   => $job->id,
            'photo_id' => $photo->id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    public function testDetachPhotoSuccess()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();

        $job = $this->fakeJobWithStatus();
        $job->photos()->attach($photo->id);
        self::assertCount(1, $job->photos);

        $url = action('Jobs\JobPhotosController@detachPhoto', [
            'job_id'   => $job->id,
            'photo_id' => $photo->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = Job::findOrFail($job->id);
        self::assertCount(0, $reloaded->photos);
    }

    public function testFailToDetachPhotoFromClosedJob()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();
        $job   = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $job->photos()->attach($photo->id);

        $url = action('Jobs\JobPhotosController@detachPhoto', [
            'job_id'   => $job->id,
            'photo_id' => $photo->id,
        ]);

        $this->deleteJson($url)->assertStatus(405);
    }

    public function testUpdatePhotoDescriptionSuccess()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();

        $job = $this->fakeJobWithStatus();
        $job->photos()->attach($photo->id);
        self::assertCount(1, $job->photos);

        $url = action('Jobs\JobPhotosController@updatePhoto', [
            'job_id'   => $job->id,
            'photo_id' => $photo->id,
        ]);

        $data = [
            'description' => $this->faker->sentence,
        ];
        $this->patchJson($url, $data)->assertStatus(200);

        $reloaded = Job::findOrFail($job->id)->photos()->findOrFail($photo->id);

        self::assertEquals($data['description'], $reloaded->pivot->description);
    }

    public function testFailToUpdatePhotoForClosedJob()
    {
        /** @var Photo $photo */
        $photo = factory(Photo::class)->create();

        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $job->photos()->attach($photo->id);
        self::assertCount(1, $job->photos);

        $url = action('Jobs\JobPhotosController@updatePhoto', [
            'job_id'   => $job->id,
            'photo_id' => $photo->id,
        ]);

        $data = [
            'description' => $this->faker->sentence,
        ];
        $this->patchJson($url, $data)->assertStatus(405);
    }

    public function testDetachMultiplePhotos()
    {
        $cnt = $this->faker->numberBetween(2, 5);
        /** @var Photo $photo */
        $photos = factory(Photo::class, $cnt)->create();

        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$activeStatuses)
        );

        foreach ($photos as $photo) {
            $job->photos()->attach($photo->id);
        }

        $url = action('Jobs\JobPhotosController@detachPhotos', [
            'job' => $job->id,
        ]);

        $data = [
            'photo_ids' => $photos->pluck('id')->toArray(),
        ];
        $this->deleteJson($url, $data)
            ->assertStatus(200);

        self::assertCount(0, $job->photos);
    }

    public function testDetachMultiplePhotosExceptOne()
    {
        $cnt = $this->faker->numberBetween(3, 5);
        /** @var Photo $photo */
        $photos = factory(Photo::class, $cnt)->create();

        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$activeStatuses)
        );

        foreach ($photos as $photo) {
            $job->photos()->attach($photo->id);
        }

        $url = action('Jobs\JobPhotosController@detachPhotos', [
            'job' => $job->id,
        ]);

        $deleteIds = [];
        for ($idx = 1; $idx < $cnt; $idx++) {
            $deleteIds[] = $photos[$idx]->id;
        }

        $data = [
            'photo_ids' => $deleteIds,
        ];
        $this->deleteJson($url, $data)
            ->assertStatus(200);

        self::assertCount(1, $job->photos);
        self::assertEquals($photos->first()->id, $job->photos->first()->id);
    }
}
